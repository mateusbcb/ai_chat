<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;
use GuzzleHttp\Client;
use App\Models\Message;

class ChatController extends Controller
{
    public function index()
    {
        // Puxa o histórico e decodifica os emojis e as quebras de linha reais
        $messages = Message::orderBy('created_at', 'asc')->get()->map(function($msg) {
            // 1. json_decode recupera os emojis
            $decoded = json_decode('"' . $msg->content . '"');
            
            // 2. Garante que os '\n' salvos como texto voltem a ser quebras de linha de verdade para o JavaScript ler
            $msg->content = trim(stripcslashes($decoded), '"');
            
            return $msg;
        });

        return view('chat', compact('messages'));
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $userMessage = $request->input('message');

        // 1. Salva a mensagem do usuário codificada no Banco de Dados
        Message::create([
            'role' => 'user',
            'content' => trim(json_encode($userMessage), '"')
        ]);

        // 2. Busca o histórico formatado e decodifica para a IA receber o emoji real
        $history = Message::orderBy('created_at', 'asc')
            ->get(['role', 'content'])
            ->map(function ($message) {
                // Decodifica o ASCII do banco de volta para emoji real antes de mandar para o LM Studio
                $decodedContent = trim(json_decode('"' . $message->content . '"'), '"');
                
                return [
                    'role' => trim($message->role),
                    'content' => $decodedContent
                ];
            })
            ->toArray();

        return new StreamedResponse(function () use ($history) {
            $client = new Client();
            
            $baseUrl = env('LM_STUDIO_BASE_URL', 'http://localhost:1234/v1');
            $model = env('LM_STUDIO_MODEL');

            $response = $client->post($baseUrl . '/chat/completions', [
                'json' => [
                    'model' => $model,
                    'messages' => $history,
                    'stream' => true
                ],
                'stream' => true
            ]);

            $body = $response->getBody();
            $fullResponseText = "";

            // Lendo o stream de forma nativa e ultra-compatível
            while (!$body->eof()) {
                // Lê até encontrar uma quebra de linha nativa do stream
                $line = '';
                while (!$body->eof()) {
                    $char = $body->read(1);
                    if ($char === "\n") {
                        break;
                    }
                    $line .= $char;
                }
                $line = trim($line);

                if (strpos($line, 'data: ') === 0) {
                    $dataText = substr($line, 6);
                    
                    if ($dataText === '[DONE]') {
                        break;
                    }

                    $data = json_decode($dataText, true);
                    if (isset($data['choices'][0]['delta']['content'])) { // <-- Ajustado o índice [0] que varia em alguns modelos
                        $content = $data['choices'][0]['delta']['content'];
                        $fullResponseText .= $content;

                        echo $content;
                        
                        // Força o PHP e o Servidor Apache/Nginx/Artisan a cuspirem o caractere imediatamente
                        if (ob_get_level() > 0) {
                            ob_flush();
                        }
                        flush();
                    }
                }
            }

            // 3. Salva a resposta gerada pela IA convertendo emojis em sequências ASCII seguras
            if (!empty(trim($fullResponseText))) {
                // Transforma "😊" em "\u1f60a" (Texto puro que cabe no seu UTF8)
                $safeText = trim(json_encode($fullResponseText), '"');

                Message::create([
                    'role' => 'assistant',
                    'content' => $safeText
                ]);
            }
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function clearChat()
    {
        // Deleta todas as mensagens do banco de dados
        Message::truncate();
        return response()->json(['status' => 'success']);
    }

    private function readLine($stream) {
        $buffer = '';
        while (!$stream->eof()) {
            $char = $stream->read(1);
            if ($char === "\n") {
                break;
            }
            $buffer .= $char;
        }
        return trim($buffer);
    }
}
