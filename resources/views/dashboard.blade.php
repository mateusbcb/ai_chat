@extends('layouts.app')



@section('content')
<!-- Cabeçalho Interno do Dashboard -->
<div class="bg-slate-800 border-b border-slate-700 p-6 px-8 flex justify-between items-center flex-shrink-0">
    <div>
        <h2 class="font-bold text-xl text-white tracking-wide">Painel de Controle Geral</h2>
        <p class="text-xs text-slate-400 mt-0.5">Visão unificada das métricas da Nexus Logística</p>
    </div>
    <div class="text-xs font-mono text-slate-500 bg-slate-950/40 border border-slate-700/60 px-3 py-1.5 rounded-lg">
        Status do Sistema: <span class="text-emerald-400 font-bold">Online</span>
    </div>
</div>

<!-- Grid de Cards Indicadores -->
<div class="flex-grow p-8 overflow-y-auto space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Card Produtos -->
        <div class="bg-slate-800 border border-slate-700/70 p-5 rounded-2xl shadow-md flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-medium uppercase tracking-wider">Produtos Ativos</span>
                <h3 class="text-3xl font-black text-white mt-1.5">{{ $totalProdutos }}</h3>
            </div>
            <div class="text-2xl p-3 bg-slate-900 rounded-xl">📦</div>
        </div>

        <!-- Card Total em Estoque -->
        <div class="bg-slate-800 border border-slate-700/70 p-5 rounded-2xl shadow-md flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-medium uppercase tracking-wider">Volume de Estoque</span>
                <h3 class="text-3xl font-black text-blue-400 mt-1.5">{{ number_format($totalEstoque, 0, ',', '.') }}</h3>
            </div>
            <div class="text-2xl p-3 bg-slate-900 rounded-xl">📈</div>
        </div>

        <!-- Card Fornecedores -->
        <div class="bg-slate-800 border border-slate-700/70 p-5 rounded-2xl shadow-md flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-medium uppercase tracking-wider">Fornecedores Homologados</span>
                <h3 class="text-3xl font-black text-white mt-1.5">{{ $totalFornecedores }}</h3>
            </div>
            <div class="text-2xl p-3 bg-slate-900 rounded-xl">🚚</div>
        </div>

        <!-- Card Lojas/Franquias -->
        <div class="bg-slate-800 border border-slate-700/70 p-5 rounded-2xl shadow-md flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-medium uppercase tracking-wider">Franquias Ativas</span>
                <h3 class="text-3xl font-black text-emerald-400 mt-1.5">{{ $totalLojas }}</h3>
            </div>
            <div class="text-2xl p-3 bg-slate-900 rounded-xl">🏢</div>
        </div>

    </div>

    <!-- Banner Informativo -->
    <div class="bg-slate-800/40 border border-slate-700/40 rounded-2xl p-6 flex flex-col gap-2 max-w-3xl">
        <h4 class="font-bold text-white text-sm">💡 Assistente Integrado Disponível</h4>
        <p class="text-xs text-slate-400 leading-relaxed">Você pode clicar no botão <span class="text-blue-400 font-medium">Assistente Virtual</span> no menu lateral para abrir o chat inteligente. O modelo local está alimentado com os dados desse painel e responderá dúvidas sobre as franquias e produtos cadastrados.</p>
    </div>
</div>
@endsection