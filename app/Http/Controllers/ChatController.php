<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use GuzzleHttp\Client;
use App\Models\Message;
use App\Models\ChatSession;

class ChatController extends Controller
{
    public function index($id = null)
    {
        // Busca todas as abas/conversas para a barra lateral
        $sessions = ChatSession::orderBy('updated_at', 'desc')->get();

        // Se não houver nenhuma aba criada, cria a primeira automaticamente
        if ($sessions->isEmpty()) {
            $newSession = ChatSession::create(['title' => 'Nova conversa']);
            return redirect()->route('chat.index', $newSession->id);
        }

        // Se não foi passado ID na URL, pega o ID da conversa mais recente
        if (!$id) {
            return redirect()->route('chat.index', $sessions->first()->id);
        }

        $currentSession = ChatSession::findOrFail($id);
        
        // Puxa e decodifica as mensagens EXCLUSIVAS desta sessão/aba
        $messages = Message::where('chat_session_id', $id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function($msg) {
                $decoded = json_decode('"' . $msg->content . '"');
                $msg->content = trim(stripcslashes($decoded), '"');
                return $msg;
            });

        return view('chat', compact('sessions', 'currentSession', 'messages'));
    }

    public function newSession()
    {
        $session = ChatSession::create(['title' => 'Nova conversa']);
        return response()->json(['status' => 'success', 'redirect' => route('chat.index', $session->id)]);
    }

    /**
     * Rota 1: Analisa se há necessidade de ferramentas usando o modelo de TOOLS.
     */
    public function sendMessage(Request $request, $id)
    {
        $request->validate(['message' => 'required|string']);
        $userMessage = $request->input('message');
        
        $session = \App\Models\ChatSession::findOrFail($id);

        if ($session->title === 'Nova conversa') {
            $session->update(['title' => substr($userMessage, 0, 25) . '...']);
        } else {
            $session->touch();
        }

        // Salva a mensagem do usuário no MySQL
        \App\Models\Message::create([
            'role' => 'user',
            'content' => trim(json_encode($userMessage), '"'),
            'chat_session_id' => $id
        ]);

        // Carrega o histórico acumulado
        $rawHistory = \App\Models\Message::where('chat_session_id', $id)
            ->orderBy('created_at', 'asc')
            ->get(['role', 'content'])
            ->map(function ($message) {
                return [
                    'role' => trim($message->role),
                    'content' => trim(json_decode('"' . $message->content . '"'), '"')
                ];
            })
            ->toArray();

        $systemPromptPath = resource_path('markdown/project_context.md');
        $systemContent = file_exists($systemPromptPath) ? file_get_contents($systemPromptPath) : '';

        // MONTADOR DE HISTÓRICO CORRETO
        $history = [['role' => 'system', 'content' => trim($systemContent)]];
        foreach ($rawHistory as $msg) {
            $lastIdx = count($history) - 1;
            if ($lastIdx >= 0 && $history[$lastIdx]['role'] === $msg['role']) {
                $history[$lastIdx]['content'] .= "\n" . $msg['content'];
            } else {
                $history[] = $msg;
            }
        }

        $client = new \GuzzleHttp\Client();
        
        // CONFIGURADOR DINÂMICO DE PROVEDOR (LOCAL VS OPENAI)
        $provider = env('AI_PROVIDER', 'local');
        $headers = ['Content-Type' => 'application/json'];

        if ($provider === 'openai') {
            $baseUrl    = env('OPENAI_BASE_URL', 'https://api.openai.com/v1');
            $modelTools = env('OPENAI_MODEL', 'gpt-4o-mini');
            $headers['Authorization'] = 'Bearer ' . env('OPENAI_API_KEY');
        } else {
            $baseUrl    = env('LM_STUDIO_BASE_URL', 'http://localhost:1234/v1');
            $modelTools = env('LM_STUDIO_MODEL_TOOLS');
        }

        $toolsService = new \App\Services\CrmToolsService();
        $toolsManifest = $toolsService->getToolsManifest();

        try {
            // Requisição de análise estática de intenção de ferramentas
            $response = $client->post($baseUrl . '/chat/completions', [
                'headers' => $headers,
                'json' => [
                    'model' => $modelTools,
                    'messages' => $history,
                    'tools' => $toolsManifest,
                    'tool_choice' => 'auto',
                    'stream' => false
                ],
                'http_errors' => true,
                'verify' => false
            ]);

            $responseData = json_decode($response->getBody(), true);
            $choiceMessage = $responseData['choices'][0]['message'] ?? null;

            // BLINDAGEM: Garante que as tool_calls existam e NÃO venham como array vazio
            if ($choiceMessage && isset($choiceMessage['tool_calls']) && !empty($choiceMessage['tool_calls'])) {
                $rawCalls = $choiceMessage['tool_calls'];
                $toolCall = is_array($rawCalls) ? $rawCalls[0] : $rawCalls;
                $functionName = $toolCall['function']['name'] ?? null;

                if ($functionName) {
                    $arguments = is_string($toolCall['function']['arguments'] ?? '') 
                        ? json_decode($toolCall['function']['arguments'], true) 
                        : ($toolCall['function']['arguments'] ?? []);

                    // Executa o serviço que consulta ou altera as tabelas do MySQL
                    $toolResult = $toolsService->execute($functionName, $arguments);

                    return response()->json([
                        'status' => 'tool_call',
                        'choice_message' => $choiceMessage,
                        'tool_call_id' => $toolCall['id'] ?? 'call_123',
                        'function_name' => $functionName,
                        'tool_result' => $toolResult
                    ]);
                }
            }

            return response()->json(['status' => 'regular_chat']);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Rota 2: Executa o streaming de texto (Server-Sent Events) compatível com LM Studio e OpenAI.
     */
    public function streamResponse(Request $request, $id)
    {
        // Força o PHP a não derrubar a conexão por timeout durante o streaming
        set_time_limit(0);
        
        $historyInput = $request->input('history', []);

        // Se o histórico veio vazio do Front (Conversa comum), reconstrói a base do MySQL
        if (empty($historyInput)) {
            $rawHistory = \App\Models\Message::where('chat_session_id', $id)
                ->orderBy('created_at', 'asc')
                ->get(['role', 'content'])
                ->map(function ($message) {
                    return [
                        'role' => trim($message->role),
                        'content' => trim(json_decode('"' . $message->content . '"'), '"')
                    ];
                })
                ->toArray();

            $systemPromptPath = resource_path('markdown/project_context.md');
            $systemContent = file_exists($systemPromptPath) ? file_get_contents($systemPromptPath) : '';

            $historyInput = [['role' => 'system', 'content' => trim($systemContent)]];
            foreach ($rawHistory as $msg) {
                $lastIdx = count($historyInput) - 1;
                if ($lastIdx >= 0 && $historyInput[$lastIdx]['role'] === $msg['role']) {
                    $historyInput[$lastIdx]['content'] .= "\n" . $msg['content'];
                } else {
                    $historyInput[] = $msg;
                }
            }
        }

        // MÁSCARA DE JINJA: Converte o papel 'tool' em 'user' para evitar erro 400 no Gemma
        if (!empty($historyInput)) {
            foreach ($historyInput as $key => $msg) {
                if (isset($msg['role']) && $msg['role'] === 'tool') {
                    $historyInput[$key]['role'] = 'user';
                    $historyInput[$key]['content'] = "[DADOS REAIS DO SISTEMA WMS]:\n" . $msg['content'];
                }
            }
        }

        return new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($historyInput, $id) {
            $client = new \GuzzleHttp\Client();

            $provider = env('AI_PROVIDER', 'local');
            $headers = [
                'Content-Type'  => 'application/json',
                'Cache-Control' => 'no-cache',
            ];

            if ($provider === 'openai') {
                $baseUrl   = rtrim(env('OPENAI_BASE_URL', 'https://api.openai.com/v1'), '/');
                $modelChat = env('OPENAI_MODEL', 'gpt-4o-mini');
                $headers['Authorization'] = 'Bearer ' . env('OPENAI_API_KEY');
            } else {
                $baseUrl   = rtrim(env('LM_STUDIO_BASE_URL', 'http://localhost:1234/v1'), '/');
                $modelChat = env('LM_STUDIO_MODEL_CHAT');
            }

            try {
                // Abre a requisição de streaming com a API
                $response = $client->post($baseUrl . '/chat/completions', [
                    'headers'     => $headers,
                    'json'        => [
                        'model'    => $modelChat,
                        'messages' => $historyInput,
                        'stream'   => true
                    ],
                    'stream'      => true,
                    'http_errors' => true,
                    'verify'      => false // Mantido falso para evitar travas de ambiente local
                ]);

                $body = $response->getBody();
                $fullResponseText = "";
                $buffer = "";

                // CORREÇÃO MESTRA: Lê os pacotes da rede em blocos de até 1024 bytes (Blindagem HTTPS)
                while (!$body->eof()) {
                    $chunk = $body->read(1024);
                    $buffer .= $chunk;

                    // Quebra o buffer acumulado por linhas reais de rede (\n)
                    while (($pos = strpos($buffer, "\n")) !== false) {
                        $line = substr($buffer, 0, $pos);
                        $buffer = substr($buffer, $pos + 1);
                        $line = trim($line);

                        // Se a linha começar com o marcador de dados do EventStream
                        if (strpos($line, 'data: ') === 0) {
                            $dataText = trim(substr($line, 6));

                            // Se chegou ao fim do streaming oficial da OpenAI
                            if ($dataText === '[DONE]') {
                                break 2;
                            }

                            $data = json_decode($dataText, true);
                            $content = $data['choices'][0]['delta']['content'] ?? null;

                            if ($content !== null) {
                                $fullResponseText .= $content;

                                // Cospe o fragmento de texto imediatamente para o JavaScript na tela
                                echo $content;
                                
                                if (ob_get_level() > 0) {
                                    ob_flush();
                                }
                                flush();
                            }
                        }
                    }
                }

                // Grava o consolidado final da resposta da IA no banco de dados MySQL
                if (!empty(trim($fullResponseText))) {
                    \App\Models\Message::create([
                        'role'            => 'assistant',
                        'content'         => trim(json_encode($fullResponseText), '"'),
                        'chat_session_id' => $id
                    ]);
                }

            } catch (\Exception $e) {
                echo "Erro no streaming final: " . $e->getMessage();
            }
        }, 200, [
            'Cache-Control'     => 'no-cache',
            'Content-Type'      => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function clearChat($id)
    {
        // 1. Deleta manualmente todas as mensagens associadas a esta aba de chat
        Message::where('chat_session_id', $id)->delete();

        // 2. Exclui a aba de chat em si
        ChatSession::destroy($id);

        return response()->json(['status' => 'success', 'redirect' => route('chat.index')]);
    }
}