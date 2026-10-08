<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AI Local Chat - Laravel 8</title>

    <!-- TailwindCSS -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <!-- Marked.js para converter Markdown em HTML -->
    <script src="https://cdn.jsdelivr.net/npm/marked/lib/marked.umd.js"></script>
</head>

 <style>
    /* Ajustes tipográficos para elementos Markdown dentro dos balões */
    .prose strong { color: inherit; font-weight: 700; }
    .prose a { color: #38bdf8; text-decoration: underline; }
    .prose p { margin-bottom: 0.5rem; }
    .prose p:last-child { margin-bottom: 0; }
    .prose h1, .prose h2, .prose h3 { font-weight: 700; color: inherit; margin-top: 0.5rem; margin-bottom: 0.25rem; }
    .prose h1 { font-size: 1.25rem; }
    .prose h2 { font-size: 1.15rem; }
    .prose h3 { font-size: 1.05rem; }
    .prose ul { list-style-type: disc; margin-left: 1.25rem; margin-bottom: 0.5rem; }
    .prose ol { list-style-type: decimal; margin-left: 1.25rem; margin-bottom: 0.5rem; }
    
    /* Força a quebra automática de palavras extensas para evitar quebra de layout */
    .markdown-content {
        word-break: break-word;
        overflow-wrap: break-word;
        white-space: pre-wrap;
    }

    /* Estilização sutil da barra de rolagem */
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #475569; border-radius: 10px; }
</style>

<body class="bg-slate-900 h-screen flex items-center justify-center p-4">
    <!-- Container Principal Expandido para suportar o Painel Lateral -->
    <div class="flex w-full max-w-6xl h-[85vh] bg-slate-800 shadow-2xl rounded-2xl overflow-hidden border border-slate-700">
        
        <!-- BARRA LATERAL (Controle de Abas/Chats) -->
        <div class="w-64 bg-slate-900 border-r border-slate-700 flex flex-col justify-between flex-shrink-0">
            <div class="p-4 flex flex-col flex-grow overflow-hidden">
                <!-- Botão de Nova Conversa -->
                <button id="new-chat-btn" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm py-2.5 px-4 rounded-xl shadow-md transition-all flex items-center justify-center gap-2 mb-4 flex-shrink-0 cursor-pointer">
                    <svg xmlns="http://w3.org" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Novo Chat
                </button>

                <!-- Lista de Conversas Ativas no Banco de Dados -->
                <div class="flex-grow overflow-y-auto space-y-1 pr-1">
                    @foreach($sessions as $session)
                        <a href="{{ route('chat.index', $session->id) }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all group truncate {{ $session->id == $currentSession->id ? 'bg-slate-800 text-white border border-slate-700' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }}">
                            <!-- Ícone de Balão de Chat -->
                            <svg xmlns="http://w3.org" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 flex-shrink-0 text-slate-500 group-hover:text-slate-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                            </svg>
                            <span class="truncate">{{ $session->title }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Rodapé da Barra Lateral -->
            <div class="p-4 border-t border-slate-800 text-center">
                <span class="text-[10px] text-slate-500 font-mono tracking-wider uppercase">Local Environment</span>
            </div>
        </div>

        <!-- CONTEÚDO DO CHAT ATIVO -->
        <div class="flex flex-col flex-grow bg-slate-800 overflow-hidden">
            
            <!-- Sub-Header da Janela Ativa -->
            <div class="bg-slate-800 border-b border-slate-700 p-4 flex justify-between items-center px-6 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></div>
                    <div>
                        <h1 class="font-bold text-white text-base tracking-wide truncate max-w-xs md:max-w-md">{{ $currentSession->title }}</h1>
                        <p class="text-xs text-slate-400">Modelo: {{ env('LM_STUDIO_MODEL', 'Gemma 3') }}</p>
                    </div>
                </div>
                <!-- Botão agora limpa/deleta a aba ativa inteira -->
                <button id="clear-btn" class="text-slate-400 hover:text-rose-400 text-xs font-medium border border-slate-700 hover:border-rose-500/30 px-3 py-1.5 rounded-lg transition-all duration-200 bg-slate-900/50">Excluir Chat</button>
            </div>
        <!-- Área de Conversa (Mensagens desta Aba Específica) -->
        <div id="chat-box" class="flex-grow p-6 overflow-y-auto space-y-6 bg-slate-950/20">
            @foreach($messages as $message)
                @if($message->role === 'user')
                    <!-- Balão do Usuário (Direita e Azul) -->
                    <div class="flex justify-end gap-3 pl-12">
                        <div class="flex flex-col items-end">
                            <div class="max-w-xl p-3.5 rounded-2xl rounded-tr-none bg-blue-600 text-white text-sm shadow-md prose markdown-content break-words">{{ $message->content }}</div>
                            <span class="text-[10px] text-slate-500 mt-1 mr-1">Você</span>
                        </div>
                    </div>
                @else
                    <!-- Balão da IA (Esquerda e Cinza) -->
                    <div class="flex justify-start gap-3 pr-12">
                        <div class="flex flex-col items-start max-w-full">
                            <div class="max-w-xl p-3.5 rounded-2xl rounded-tl-none bg-slate-700 text-slate-100 text-sm border border-slate-600/50 shadow-md prose markdown-content break-words">{{ $message->content }}</div>
                            <span class="text-[10px] text-slate-500 mt-1 ml-1">Assistente</span>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <!-- Rodapé Fixo com Textarea de 5 linhas -->
        <div class="p-4 bg-slate-800 border-t border-slate-700 flex-shrink-0">
            <form id="chat-form" class="flex gap-3 w-full items-end bg-slate-900 rounded-xl border border-slate-700 p-3 focus-within:border-blue-500 transition-all">
                <textarea id="message-input" placeholder="Envie uma mensagem para a IA... (Shift+Enter quebra linha)" class="flex-grow bg-transparent text-slate-200 placeholder-slate-500 text-sm px-2 focus:outline-none resize-none h-auto pt-1 w-full" rows="3" required></textarea>
                <button type="submit" id="send-btn" class="bg-blue-600 hover:bg-blue-500 disabled:bg-slate-800 text-white disabled:text-slate-600 p-3 rounded-lg font-semibold transition-all shadow-lg flex items-center justify-center disabled:cursor-not-allowed mb-1 cursor-pointer">
                    <svg xmlns="http://w3.org" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                    </svg>
                </button>
            </form>
        </div>
    </div>

     <!-- Lógica JavaScript de Controle e Abas -->
    <script>
        const chatForm = document.getElementById('chat-form');
        const messageInput = document.getElementById('message-input');
        const chatBox = document.getElementById('chat-box');
        const clearBtn = document.getElementById('clear-btn');
        const sendBtn = document.getElementById('send-btn');
        const newChatBtn = document.getElementById('new-chat-btn');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // 1. Enviar com Enter / Shift+Enter quebra linha no textarea de 5 linhas
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                chatForm.dispatchEvent(new Event('submit'));
            }
        });

        // 2. Evento para Criar um Novo Chat (Nova Aba)
        newChatBtn.addEventListener('click', async () => {
            const response = await fetch("{{ route('chat.new') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken }
            });
            const data = await response.json();
            if (data.status === 'success') {
                window.location.href = data.redirect; // Redireciona para o ID da nova aba
            }
        });

        // 3. Evento principal de envio de mensagens por streaming
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const message = messageInput.value.trim();
            if (!message) return;

            // Bloqueia interações enquanto a resposta carrega
            messageInput.disabled = true;
            sendBtn.disabled = true;

            // Insere o balão do usuário na tela instantaneamente
            const userMessageId = appendMessage('', 'user');
            document.getElementById(userMessageId).querySelector('.markdown-content').innerHTML = marked.parse(message);
            
            messageInput.value = '';
            
            // Cria o balão vazio da IA aguardando fragmentos
            const botMessageId = appendMessage('', 'bot');
            const botMessageDiv = document.getElementById(botMessageId).querySelector('.markdown-content');

            try {
                // Passa o ID da sessão ativa na rota de envio
                const response = await fetch("{{ route('chat.send', $currentSession->id) }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ message: message })
                });

                if (!response.ok) {
                    botMessageDiv.innerText = 'Erro ao processar requisição no servidor.';
                    botMessageDiv.classList.add('bg-rose-950', 'text-rose-400');
                    liberarInputs();
                    return;
                }

                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                let rawResponseText = '';

                while (true) {
                    const { value, done } = await reader.read();
                    if (done) break;

                    const chunk = decoder.decode(value, { stream: true });
                    rawResponseText += chunk;

                    botMessageDiv.innerHTML = marked.parse(rawResponseText);
                    chatBox.scrollTop = chatBox.scrollHeight;
                }

                // Recarrega a página ao concluir a primeira resposta para atualizar o título gerado na barra lateral
                if ("{{ $currentSession->title }}" === "Nova conversa") {
                    window.location.reload();
                }

            } catch (error) {
                botMessageDiv.innerText = 'Erro crítico de conexão com o stream.';
                botMessageDiv.classList.add('bg-rose-950', 'text-rose-400');
                console.error(error);
            } finally {
                liberarInputs();
            }
        });

        // 4. Deletar a Conversa/Aba ativa atual
        clearBtn.addEventListener('click', async () => {
            if(confirm('Deseja realmente excluir esta conversa e todo o seu histórico?')) {
                const response = await fetch("{{ route('chat.clear', $currentSession->id) }}", { 
                    method: 'POST', 
                    headers: { 'X-CSRF-TOKEN': csrfToken } 
                });
                const data = await response.json();
                window.location.href = data.redirect;
            }
        });

        // 5. Função de renderização dos novos balões
        function appendMessage(text, role) {
            const wrapper = document.createElement('div');
            const id = 'msg-' + Date.now() + Math.random().toString(36).substr(2, 5);
            wrapper.id = id;

            if (role === 'user') {
                wrapper.className = 'flex justify-end gap-3 pl-12';
                wrapper.innerHTML = `
                    <div class="flex flex-col items-end">
                        <div class="max-w-xl p-3.5 rounded-2xl rounded-tr-none bg-blue-600 text-white text-sm shadow-md prose markdown-content">${text}</div>
                        <span class="text-[10px] text-slate-500 mt-1 mr-1">Você</span>
                    </div>`;
            } else {
                wrapper.className = 'flex justify-start gap-3 pr-12';
                wrapper.innerHTML = `
                    <div class="flex flex-col items-start max-w-full">
                        <div class="max-w-xl p-3.5 rounded-2xl rounded-tl-none bg-slate-700 text-slate-100 text-sm border border-slate-600/50 shadow-md prose markdown-content break-words">${text}</div>
                        <span class="text-[10px] text-slate-500 mt-1 ml-1">Assistente</span>
                    </div>`;
            }

            chatBox.appendChild(wrapper);
            chatBox.scrollTop = chatBox.scrollHeight;
            return id;
        }

        function liberarInputs() {
            messageInput.disabled = false;
            sendBtn.disabled = false;
            messageInput.focus();
        }

        // 6. Formata o histórico do banco de dados ao recarregar a página
        function formatExistingMessages() {
            const messages = document.querySelectorAll('.markdown-content');
            messages.forEach(msg => {
                const rawText = msg.textContent; 
                msg.innerHTML = marked.parse(rawText);
            });
            chatBox.scrollTop = chatBox.scrollHeight;
        }
        
        formatExistingMessages();
    </script>
</body>
</html>