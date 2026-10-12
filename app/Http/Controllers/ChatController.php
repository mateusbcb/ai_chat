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
        
        $session = ChatSession::findOrFail($id);

        if ($session->title === 'Nova conversa') {
            $session->update(['title' => substr($userMessage, 0, 25) . '...']);
        } else {
            $session->touch();
        }

        // Salva a mensagem do usuário no MySQL
        Message::create([
            'role' => 'user',
            'content' => trim(json_encode($userMessage), '"'),
            'chat_session_id' => $id
        ]);

        // Carrega o histórico acumulado
        $rawHistory = Message::where('chat_session_id', $id)
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

        // MONTADOR DE HISTÓRICO CORRETO: Inicia com o System e adiciona itens no fim sem quebrar o array
        $history = [['role' => 'system', 'content' => trim($systemContent)]];
        foreach ($rawHistory as $msg) {
            $lastIdx = count($history) - 1;
            if ($lastIdx >= 0 && $history[$lastIdx]['role'] === $msg['role']) {
                $history[$lastIdx]['content'] .= "\n" . $msg['content'];
            } else {
                $history[] = $msg; // <-- CORREÇÃO: Uso do colchete [] para empurrar no fim do array de forma nativa
            }
        }

        $client = new Client();
        $baseUrl = env('LM_STUDIO_BASE_URL', 'http://localhost:1234/v1');
        $modelTools = env('LM_STUDIO_MODEL_TOOLS'); 
        
        $toolsService = new \App\Services\CrmToolsService();
        $toolsManifest = $toolsService->getToolsManifest();

        try {
            // Requisição estática de análise de intenção de ferramentas
            $response = $client->post($baseUrl . '/chat/completions', [
                'json' => [
                    'model' => $modelTools,
                    'messages' => $history,
                    'tools' => $toolsManifest,
                    'tool_choice' => 'auto',
                    'stream' => false
                ],
                'http_errors' => false
            ]);

            $responseData = json_decode($response->getBody(), true);
            $choiceMessage = $responseData['choices'][0]['message'] ?? null;

            if ($choiceMessage && isset($choiceMessage['tool_calls'])) {
                $rawCalls = $choiceMessage['tool_calls'];
                $toolCall = isset($rawCalls[0]) ? $rawCalls[0] : $rawCalls;
                $functionName = $toolCall['function']['name'] ?? $choiceMessage['function']['name'] ?? null;

                if ($functionName) {
                    $arguments = is_string($toolCall['function']['arguments'] ?? '') 
                        ? json_decode($toolCall['function']['arguments'], true) 
                        : ($toolCall['function']['arguments'] ?? []);

                    // Executa o serviço que consulta as tabelas do MySQL reais
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
     * Rota 2: Executa exclusivamente o streaming de texto com o modelo focado em CHAT.
     */
    public function streamResponse(Request $request, $id)
    {
        set_time_limit(0);
        $historyInput = $request->input('history', []);

        // Se o histórico não veio processado do Front, reconstrói o padrão local de segurança
        if (empty($historyInput)) {
            $rawHistory = Message::where('chat_session_id', $id)
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
                    $historyInput[] = $msg; // <-- CORREÇÃO: Uso de [] garantido também na reconstrução
                }
            }
        }

        return new StreamedResponse(function () use ($historyInput, $id) {
            $client = new Client();
            $baseUrl = env('LM_STUDIO_BASE_URL', 'http://localhost:1234/v1');
            $modelChat = env('LM_STUDIO_MODEL_CHAT'); 

            try {
                // CORREÇÃO: Endpoint limpo unificado sem repetições de /v1/v1
                $response = $client->post($baseUrl . '/chat/completions', [
                    'json' => [
                        'model' => $modelChat,
                        'messages' => $historyInput,
                        'stream' => true
                    ],
                    'stream' => true,
                    'http_errors' => false
                ]);

                $body = $response->getBody();
                $fullResponseText = "";

                while (!$body->eof()) {
                    $line = '';
                    while (!$body->eof()) {
                        $char = $body->read(1);
                        if ($char === "\n") break;
                        $line .= $char;
                    }
                    $line = trim($line);

                    if (strpos($line, 'data: ') === 0) {
                        $dataText = substr($line, 6);
                        if ($dataText === '[DONE]') break;

                        $data = json_decode($dataText, true);
                        if (isset($data['choices'][0]['delta']['content'])) {
                            $content = $data['choices'][0]['delta']['content'];
                            $fullResponseText .= $content;

                            echo $content;
                            if (ob_get_level() > 0) ob_flush();
                            flush();
                        }
                    }
                }

                if (!empty(trim($fullResponseText))) {
                    Message::create([
                        'role' => 'assistant',
                        'content' => trim(json_encode($fullResponseText), '"'),
                        'chat_session_id' => $id
                    ]);
                }

            } catch (\Exception $e) {
                echo "Erro no streaming final: " . $e->getMessage();
            }
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
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