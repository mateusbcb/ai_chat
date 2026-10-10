<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nexus CRM - @yield('title', 'Dashboard')</title>
    
    <!-- TailwindCSS -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <!-- Marked.js para converter Markdown em HTML -->
    <script src="https://cdn.jsdelivr.net/npm/marked/lib/marked.umd.js"></script>
    
    <style>
        /* Ajuste fino para a barra de rolagem global do sistema */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #475569; border-radius: 10px; }

        /* Estilos para o Markdown dentro do popup */
        .prose strong { color: inherit; font-weight: 700; }
        .prose a { color: #38bdf8; text-decoration: underline; }
        .prose p { margin-bottom: 0.5rem; }
        .prose p:last-child { margin-bottom: 0; }
        
        /* Força quebra automática de palavras para não estourar o balão do popup */
        .markdown-content {
            word-break: break-word;
            overflow-wrap: break-word;
            white-space: pre-wrap;
        }

        /* Classe utilitária para ocultar o chat com efeito invisível/interativo */
        .chat-hidden {
            opacity: 0;
            pointer-events: none;
            transform: translateY(20px) scale(0.95);
        }
        
        .chat-visible {
            opacity: 1;
            pointer-events: all;
            transform: translateY(0) scale(1);
        }
    </style>
    @yield('styles')
</head>
<body class="bg-slate-900 text-slate-100 h-screen flex overflow-hidden">

    <!-- BARRA LATERAL DE NAVEGAÇÃO GLOBAL (SIDEBAR) -->
    <aside class="w-64 bg-slate-950 border-r border-slate-800 flex flex-col justify-between flex-shrink-0">
        <div class="p-5 flex flex-col flex-grow overflow-hidden">
            <!-- Header do Menu -->
            <div class="flex items-center gap-3 mb-8 px-2 flex-shrink-0">
                <div class="w-3 h-3 rounded-full bg-blue-500 animate-pulse"></div>
                <div>
                    <h1 class="font-bold text-white text-base tracking-wide uppercase">Nexus CRM</h1>
                    <p class="text-[10px] text-slate-500 font-mono">v1.0 - Local Studio</p>
                </div>
            </div>

            <!-- Links de Navegação Ativos/Inativos dinamicamente -->
            <nav class="flex-grow space-y-1.5 overflow-y-auto pr-1">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all {{ Route::is('dashboard') ? 'bg-slate-800 text-white border border-slate-700 shadow-md' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }}">
                    📊 Dashboard
                </a>
                <a href="{{ route('products.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all {{ Route::is('products.*') ? 'bg-slate-800 text-white border border-slate-700 shadow-md' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }}">
                    📦 Produtos
                </a>
                <a href="{{ route('stores.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all {{ Route::is('stores.*') ? 'bg-slate-800 text-white border border-slate-700 shadow-md' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }}">
                    🏢 Lojas
                </a>
                <a href="{{ route('suppliers.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all {{ Route::is('suppliers.*') ? 'bg-slate-800 text-white border border-slate-700 shadow-md' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }}">
                    🚚 Fornecedores
                </a>
                <div class="pt-4 mt-4 border-t border-slate-800 flex-shrink-0">
                    <!-- Botão do menu agora dispara a abertura via ID e não recarrega a página -->
                    <button id="menu-chat-btn" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white shadow-lg cursor-pointer">
                        🤖 Assistente Virtual
                    </button>
                </div>
            </nav>
        </div>

        <!-- Footer da Barra Lateral -->
        <div class="p-4 border-t border-slate-800 text-center flex-shrink-0">
            <span class="text-[10px] text-slate-600 font-mono tracking-wider uppercase">Ambiente de Operação</span>
        </div>
    </aside>

    <!-- ÁREA DE CONTEÚDO INDIVIDUAL -->
    <main class="flex-grow flex flex-col overflow-hidden h-full">
        @yield('content')
    </main>

    @yield('scripts')

    <!-- ========================================== -->
    <!-- ELEMENTOS DO CHAT DE IA FLUTUANTE (POPUP) -->
    <!-- ========================================== -->

    <!-- BOTÃO FLUTUANTE REDONDO (FAB) -->
    <a href="{{ route('chat.index') }}" id="floating-chat-btn" class="fixed bottom-6 right-6 z-50 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white p-4 rounded-full shadow-2xl transition-all duration-300 transform hover:scale-105 active:scale-95 flex items-center justify-center border border-blue-500/20">
        <svg xmlns="http://w3.org" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.023c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
        </svg>
    </a>

    <!-- SE ESTIVER NA ROTA DO CHAT, ELE ABRE COMO POPUP FLUTUANTE EM CIMA DO CONTEÚDO -->
    @if(Route::is('chat.index'))
        <div id="popup-chat-window" class="fixed bottom-24 right-6 z-50 w-full max-w-4xl h-[70vh] bg-slate-800 shadow-2xl rounded-2xl overflow-hidden border border-slate-700 flex transition-all duration-300">
            <!-- Injeta o conteúdo do chat.blade.php de forma limpa -->
            @yield('chat_content')
            
            <!-- Botão de Fechar Unificado (X) - Agora usa histórico do navegador para voltar exatamente de onde você estava -->
            <button onclick="fecharPopupChat()" class="absolute top-4 right-4 text-slate-400 hover:text-white p-1 rounded-md hover:bg-slate-700/50 transition-all z-50 cursor-pointer">
                <svg xmlns="http://w3.org" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <script>
            // Função simples para fechar e voltar para a página que o usuário estava navegando antes
            function fecharPopupChat() {
                if (document.referrer.includes(window.location.hostname)) {
                    window.history.back(); // Volta para a tela exata (Dashboard, Produtos, etc.)
                } else {
                    window.location.href = "{{ route('dashboard') }}"; // Fallback de segurança
                }
            }
        </script>
    @endif
</body>
</html>