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
        /* Ajustes finos para elementos internos do Markdown nos balões */
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
        
        /* Otimização da barra de rolagem */
        #chat-box::-webkit-scrollbar { width: 6px; }
        #chat-box::-webkit-scrollbar-track { background: transparent; }
        #chat-box::-webkit-scrollbar-thumb { background: #475569; border-radius: 10px; }
    
        /* Força quebra automática de texto longo em qualquer balão do chat */
        .markdown-content {
            word-break: break-word;
            overflow-wrap: break-word;
            white-space: pre-wrap;
        }
    </style>
</head>
<body class="bg-slate-900 h-screen flex items-center justify-center p-4">

    <!-- Container Principal com Largura Máxima Definida (max-w-4xl) -->
    <div class="flex flex-col w-full max-w-4xl h-[85vh] bg-slate-800 shadow-2xl rounded-2xl overflow-hidden border border-slate-700">
        
        <!-- Cabeçalho do Chat -->
        <div class="bg-slate-800 border-b border-slate-700 p-4 flex justify-between items-center px-6 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></div>
                <div>
                    <h1 class="font-bold text-white text-base tracking-wide">AI Local Studio</h1>
                    <p class="text-xs text-slate-400">Modelo: {{ env('LM_STUDIO_MODEL', 'Gemma 3') }}</p>
                </div>
            </div>
            <button id="clear-btn" class="text-slate-400 hover:text-rose-400 text-xs font-medium border border-slate-700 hover:border-rose-500/30 px-3 py-1.5 rounded-lg transition-all duration-200 bg-slate-900/50">Limpar Conversa</button>
        </div>

        <!-- Área de Mensagens com scroll automático -->
        <div id="chat-box" class="flex-grow p-6 overflow-y-auto space-y-6 bg-slate-950/20">
            <!-- Loop Blade: Identifica o papel (role) de cada mensagem salva no banco -->
            @foreach($messages as $message)
                @if($message->role === 'user')
                    <!-- CONDICIONAL IF: Se for usuário, joga para a DIREITA e aplica cor AZUL -->
                    <div class="flex justify-end gap-3 pl-12">
                        <div class="flex flex-col items-end">
                            <div class="max-w-xl p-3.5 rounded-2xl rounded-tr-none bg-blue-600 text-white text-sm shadow-md prose markdown-content">{{ $message->content }}</div>
                            <span class="text-[10px] text-slate-500 mt-1 mr-1">Você</span>
                        </div>
                    </div>
                @else
                    <!-- CONDICIONAL ELSE: Se for assistente, joga para a ESQUERDA e aplica cor CINZA -->
                    <div class="flex justify-start gap-3 pr-12">
                        <div class="flex flex-col items-start">
                            <!-- Balão da IA (Histórico) -->
                            <div class="max-w-xl p-3.5 rounded-2xl rounded-tl-none bg-slate-700 text-slate-100 text-sm border border-slate-600/50 shadow-md prose markdown-content break-words">{{ $message->content }}</div>
                            <span class="text-[10px] text-slate-500 mt-1 ml-1">Assistente</span>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <!-- Rodapé Fixo com Caixa de Texto de 5 linhas (h-32) -->
        <div class="p-4 bg-slate-800 border-t border-slate-700 flex-shrink-0">
            <form id="chat-form" class="flex gap-3 w-full items-end bg-slate-900 rounded-xl border border-slate-700 p-3 focus-within:border-blue-500 transition-all">
                <!-- Textarea fixado em 5 linhas de altura padrão (h-32) e largura total flexível -->
                <textarea id="message-input" placeholder="Envie uma mensagem para a IA... (Shift+Enter quebra linha)" class="flex-grow bg-transparent text-slate-200 placeholder-slate-500 text-sm px-2 focus:outline-none resize-none pt-1 w-full" required></textarea>
                <button type="submit" id="send-btn" class="bg-blue-600 hover:bg-blue-500 disabled:bg-slate-800 text-white disabled:text-slate-600 p-3 rounded-lg font-semibold transition-all shadow-lg flex items-center justify-center disabled:cursor-not-allowed mb-1">
                    <!-- Vetor de Seta de Envio -->
                    <svg xmlns="http://w3.org" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <script>
        const chatForm = document.getElementById('chat-form');
        const messageInput = document.getElementById('message-input');
        const chatBox = document.getElementById('chat-box');
        const clearBtn = document.getElementById('clear-btn');
        const sendBtn = document.getElementById('send-btn');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // 1. Faz o textarea expandir automaticamente conforme digita
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 128) + 'px';
        });

        // 2. Enviar com Enter / Shift+Enter quebra linha
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                chatForm.dispatchEvent(new Event('submit'));
            }
        });

        // 3. Evento principal de envio do formulário
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const message = messageInput.value.trim();
            if (!message) return;

            // Bloquear inputs e botão de envio
            messageInput.disabled = true;
            sendBtn.disabled = true;

            // Adiciona mensagem do usuário na tela
            const userMessageId = appendMessage('', 'user');
            document.getElementById(userMessageId).querySelector('.markdown-content').innerHTML = marked.parse(message);
            
            messageInput.value = '';
            messageInput.style.height = '40px'; // Reseta altura inicial do textarea
            // Cria o balão de carregamento da IA
            const botMessageId = appendMessage('', 'bot');
            const botMessageDiv = document.getElementById(botMessageId).querySelector('.markdown-content');

            try {
                const response = await fetch("{{ route('chat.send') }}", {
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

                // Loop para ler o streaming em tempo real
                while (true) {
                    const { value, done } = await reader.read();
                    if (done) break;

                    const chunk = decoder.decode(value, { stream: true });
                    rawResponseText += chunk;

                    // Renderiza o Markdown na caixinha da IA
                    botMessageDiv.innerHTML = marked.parse(rawResponseText);
                    chatBox.scrollTop = chatBox.scrollHeight;
                }

            } catch (error) {
                botMessageDiv.innerText = 'Erro crítico de conexão com o stream.';
                botMessageDiv.classList.add('bg-rose-950', 'text-rose-400');
                console.error(error);
            } finally {
                liberarInputs();
            }
        });

        // 4. Limpar o histórico de conversas do Banco de Dados
        clearBtn.addEventListener('click', async () => {
            if(confirm('Deseja realmente apagar o histórico dessa conversa?')) {
                await fetch("{{ route('chat.clear') }}", { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } });
                chatBox.innerHTML = '';
            }
        });

        // 5. Cria dinamicamente os balões na tela (Usuário ou IA)
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
                        <!-- Adicionado 'break-words' nas classes abaixo -->
                        <div class="max-w-xl p-3.5 rounded-2xl rounded-tl-none bg-slate-700 text-slate-100 text-sm border border-slate-600/50 shadow-md prose markdown-content break-words">${text}</div>
                        <span class="text-[10px] text-slate-500 mt-1 ml-1">Assistente</span>
                    </div>`;
            }

            chatBox.appendChild(wrapper);
            chatBox.scrollTop = chatBox.scrollHeight;
            return id;
        }

        // 6. Libera os inputs ao final da resposta da IA
        function liberarInputs() {
            messageInput.disabled = false;
            sendBtn.disabled = false;
            messageInput.focus();
        }

        // 7. Formata o histórico do MySQL com Marked ao recarregar a página
        function formatExistingMessages() {
            const messages = document.querySelectorAll('.markdown-content');
            messages.forEach(msg => {
                const rawText = msg.textContent; 
                msg.innerHTML = marked.parse(rawText);
            });
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        // Execução inicial obrigatória
        formatExistingMessages();
    </script>
</body>
</html>