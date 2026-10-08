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

    public function sendMessage(Request $request, $id)
    {
        $request->validate(['message' => 'required|string']);
        $userMessage = $request->input('message');
        
        $session = ChatSession::findOrFail($id);

        // Se a conversa ainda tiver o título padrão, renomeia com o começo da primeira frase
        if ($session->title === 'Nova conversa') {
            $session->update(['title' => substr($userMessage, 0, 25) . '...']);
        } else {
            $session->touch(); // Sobe a aba atual para o topo da lista
        }

        // 1. Salva a mensagem do usuário vinculada a este ID de aba
        Message::create([
            'role' => 'user',
            'content' => trim(json_encode($userMessage), '"'),
            'chat_session_id' => $id
        ]);

        // 2. Coleta o histórico da aba e impede duplicações de papel seguidas para não quebrar o Gemma
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

        // INJEÇÃO DO SYSTEM PROMPT: Inicializa o array com as regras fixas lidas do arquivo
        $systemPromptPath = storage_path('app/project_context.txt');
        $systemContent = file_exists($systemPromptPath) 
            ? file_get_contents($systemPromptPath) 
            : 'Você é um assistente útil focado em programação.';

        $history = [
            [
                'role' => 'system',
                'content' => trim($systemContent)
            ]
        ];

        // Alimenta o array de histórico agrupando mensagens consecutivas do mesmo autor
        foreach ($rawHistory as $msg) {
            $lastIdx = count($history) - 1;
            
            // Se a última mensagem adicionada tiver o mesmo 'role', une os textos
            if ($lastIdx >= 0 && $history[$lastIdx]['role'] === $msg['role']) {
                $history[$lastIdx]['content'] .= "\n" . $msg['content'];
            } else {
                $history[] = $msg;
            }
        }

        // 3. Retorna a transmissão de texto por stream (Server-Sent Events)
        return new StreamedResponse(function () use ($history, $id) {
            $client = new Client();
            $baseUrl = env('LM_STUDIO_BASE_URL', 'http://localhost:1234/v1');
            $model = env('LM_STUDIO_MODEL');

            try {
                $response = $client->post($baseUrl . '/chat/completions', [
                    'json' => [
                        'model' => $model,
                        'messages' => $history,
                        'stream' => true
                    ],
                    'stream' => true,
                    'http_errors' => false // Impede o PHP de quebrar se o LM Studio der erro de validação 400 no final
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
                        if (isset($data['choices'][0]['delta']['content'])) { // Índice ajustado para o padrão rigoroso v1
                            $content = $data['choices'][0]['delta']['content'];
                            $fullResponseText .= $content;

                            echo $content;
                            if (ob_get_level() > 0) ob_flush();
                            flush();
                        }
                    }
                }

                // 4. Salva a resposta da IA vinculada a esta aba se houver texto gerado
                if (!empty(trim($fullResponseText))) {
                    Message::create([
                        'role' => 'assistant',
                        'content' => trim(json_encode($fullResponseText), '"'),
                        'chat_session_id' => $id
                    ]);
                } else {
                    if ($response->getStatusCode() >= 400) {
                        echo "Erro local com o modelo (Status HTTP: " . $response->getStatusCode() . ")";
                    }
                }

            } catch (\Exception $e) {
                echo "Erro de conexão física com o LM Studio: " . $e->getMessage();
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