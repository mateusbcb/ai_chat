@extends('layouts.app')

@section('content')
<!-- Cabeçalho Interno do Dashboard -->
<div class="bg-slate-800 border-b border-slate-700 p-6 px-8 flex justify-between items-center flex-shrink-0">
    <div>
        <h2 class="font-bold text-xl text-white tracking-wide">📊 Painel de Controle - Hub de Distribuição</h2>
        <p class="text-xs text-slate-400 mt-0.5">Indicadores consolidados da malha logística da Nexus Matriz</p>
    </div>
    <div class="text-xs font-mono text-slate-500 bg-slate-950/40 border border-slate-700/60 px-3 py-1.5 rounded-lg">
        WMS Status: <span class="text-emerald-400 font-bold">Monitorando</span>
    </div>
</div>

<!-- Grid de Cards Indicadores -->
<div class="flex-grow p-8 overflow-y-auto space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
        
        <!-- Faturamento Comercial Bruto -->
        <div class="bg-slate-800 border border-slate-700/70 p-5 rounded-2xl shadow-md flex items-center justify-between lg:col-span-2 bg-gradient-to-br from-slate-800 to-slate-900">
            <div>
                <span class="text-xs text-emerald-400 font-semibold uppercase tracking-wider">Faturamento de Rede Acumulado</span>
                <h3 class="text-3xl font-black text-white mt-1.5">R$ {{ number_format($faturamentoBruto, 2, ',', '.') }}</h3>
                <p class="text-[10px] text-slate-500 mt-0.5 font-medium">*Somente cupons fiscais confirmados</p>
            </div>
            <div class="text-3xl p-3 bg-emerald-950/40 border border-emerald-500/20 text-emerald-400 rounded-2xl">💰</div>
        </div>

        <!-- Volume Físico no CD Central -->
        <div class="bg-slate-800 border border-slate-700/70 p-5 rounded-2xl shadow-md flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-medium uppercase tracking-wider">Estoque no CD Pulmão</span>
                <h3 class="text-2xl font-black text-white mt-1.5">{{ number_format($estoqueCDCentral, 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">un</span></h3>
            </div>
            <div class="text-2xl p-3 bg-slate-900 rounded-xl">🏢</div>
        </div>

        <!-- Volume Físico Descentralizado nas Lojas -->
        <div class="bg-slate-800 border border-slate-700/70 p-5 rounded-2xl shadow-md flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-medium uppercase tracking-wider">Estoque nas Filiais</span>
                <h3 class="text-2xl font-black text-blue-400 mt-1.5">{{ number_format($estoqueTotalLojas, 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">un</span></h3>
            </div>
            <div class="text-2xl p-3 bg-slate-900 rounded-xl">🚚</div>
        </div>

        <!-- Contadores Cadastrais -->
        <div class="bg-slate-800 border border-slate-700/70 p-5 rounded-2xl shadow-md flex flex-col justify-center gap-1">
            <div class="text-xs text-slate-400 font-medium flex justify-between">
                <span>SKUs Ativos:</span>
                <span class="font-bold text-white">{{ $totalProdutosCatalogo }}</span>
            </div>
            <div class="text-xs text-slate-400 font-medium flex justify-between border-t border-slate-700/40 pt-1.5 mt-1.5">
                <span>Filiais Ativas:</span>
                <span class="font-bold text-white">{{ $totalLojasAtivas }}</span>
            </div>
        </div>

    </div>

    <!-- Banner Técnico Informativo -->
    <div class="bg-slate-800/40 border border-slate-700/40 rounded-2xl p-6 flex flex-col gap-2 max-w-4xl">
        <h4 class="font-bold text-white text-sm">💡 Rastreabilidade de Cadeia de Suprimentos Ativada</h4>
        <p class="text-xs text-slate-400 leading-relaxed">Este painel reflete em tempo real o fluxo físico de mercadorias. Os itens dão entrada no <span class="text-white font-medium">CD Pulmão</span>, viajam marcados como <span class="text-blue-400 font-medium">Em Trânsito</span>, são expostos <span class="text-emerald-400 font-medium">À Venda</span> nas franquias e geram a baixa automática de inventário no momento do faturamento comercial na ponta.</p>
    </div>
</div>
@endsection