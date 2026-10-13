@extends('layouts.app')

@section('content')
<!-- Cabeçalho com Barra de Busca Integrada -->
<div class="bg-slate-800 border-b border-slate-700 p-6 px-8 flex flex-col md:flex-row gap-4 justify-between items-start md:items-center flex-shrink-0">
    <div>
        <h2 class="font-bold text-xl text-white tracking-wide">📊 Auditoria & Frente de Caixa (PDV)</h2>
        <p class="text-xs text-slate-400 mt-0.5">Histórico fiscal de cupons emitidos, estornos e quebras físicas na rede</p>
    </div>
    
    <!-- Form de Busca Multicorpos e Botão Lançar -->
    <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto items-stretch sm:items-center">
        <form action="{{ route('sales.index') }}" method="GET" class="flex items-center bg-slate-900 border border-slate-700 rounded-xl px-3 py-1.5 focus-within:border-blue-500 transition-all w-full sm:w-80">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar por loja, produto, SKU ou status..." class="bg-transparent text-slate-200 placeholder-slate-500 text-xs focus:outline-none w-full pr-2">
            
            @if(!empty($search))
                <a href="{{ route('sales.index') }}" class="text-slate-500 hover:text-slate-300 mr-2 text-sm" title="Limpar busca">✕</a>
            @endif
            
            <button type="submit" class="text-slate-400 hover:text-white cursor-pointer">
                <svg xmlns="http://w3.org" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.603 10.601z" />
                </svg>
            </button>
        </form>

        <a href="{{ route('sales.create') }}" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs py-2.5 px-4 rounded-xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer whitespace-nowrap">
            + Registrar Movimentação
        </a>
    </div>
</div>

<!-- Área Principal da Tabela -->
<div class="flex-grow p-8 overflow-y-auto">
    
    @if(session('success'))
        <div class="bg-emerald-950/60 border border-emerald-500/30 text-emerald-400 p-4 rounded-xl text-xs font-semibold mb-6">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-slate-800 border border-slate-700 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900/60 border-b border-slate-700 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                        <th class="p-4 pl-6">Data/Hora (Rastreabilidade)</th>
                        <th class="p-4">Filial Emissora</th>
                        <th class="p-4">Item Movimentado</th>
                        <th class="p-4 text-center">Qtd</th>
                        <th class="p-4">Preço Unitário</th>
                        <th class="p-4">Total da Transação</th>
                        <th class="p-4 text-center">Tipo de Movimento</th>
                        <th class="p-4 pr-6 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50 text-xs text-slate-300">
                    @forelse($sales as $sale)
                        <tr class="hover:bg-slate-700/20 transition-all">
                            <!-- Rastreabilidade de Data e Hora -->
                            <td class="p-4 pl-6 font-mono text-slate-400">
                                {{ $sale->created_at ? $sale->created_at->format('d/m/Y H:i:s') : 'N/I' }}
                            </td>

                            <!-- Filial -->
                            <td class="p-4">
                                <div class="font-bold text-white text-sm">{{ $sale->store->name ?? 'Loja Excluída' }}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $sale->store->city ?? '' }}-{{ $sale->store->state ?? '' }}</div>
                            </td>

                            <!-- Produto e SKU -->
                            <td class="p-4">
                                <div class="font-semibold text-slate-200">{{ $sale->product->title ?? 'Produto Excluído' }}</div>
                                <div class="font-mono text-[10px] text-slate-500 mt-0.5">SKU: {{ $sale->product->sku ?? 'N/A' }}</div>
                            </td>

                            <!-- Quantidade -->
                            <td class="p-4 text-center font-bold text-slate-100">
                                {{ $sale->quantity }} un
                            </td>

                            <!-- Preço de Venda Unitário -->
                            <td class="p-4 text-slate-300">
                                R$ {{ number_format($sale->price_sale, 2, ',', '.') }}
                            </td>

                            <!-- Cálculo Total Acumulado -->
                            <td class="p-4 font-bold text-white">
                                @if($sale->status === 'quebra')
                                    <span class="text-rose-400 font-medium italic">Perda Total</span>
                                @else
                                    R$ {{ number_format($sale->price_sale * $sale->quantity, 2, ',', '.') }}
                                @endif
                            </td>

                            <!-- Status do Cupom com Crachás Estilizados -->
                            <td class="p-4 text-center">
                                @if($sale->status === 'vendido')
                                    <span class="bg-emerald-950/60 border border-emerald-500/30 text-emerald-400 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide">💰 Vendido</span>
                                @elseif($sale->status === 'devolvido')
                                    <span class="bg-blue-950/60 border border-blue-500/30 text-blue-400 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide">🔄 Devolvido</span>
                                @else
                                    <span class="bg-rose-950/60 border border-rose-500/30 text-rose-400 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide">⚠️ Quebra/Perda</span>
                                @endif
                            </td>

                            <!-- Botão de Anulação Exclusiva -->
                            <td class="p-4 pr-6 text-right">
                                <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" onsubmit="return confirm('Deseja realmente estornar/anular este registro do histórico fiscal?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-rose-400 font-medium transition-all px-2.5 py-1 border border-slate-700 hover:border-rose-500/20 rounded-md bg-slate-900/30 cursor-pointer">Anular</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-500 italic">Nenhum cupom fiscal ou movimentação de caixa registrada nas filiais.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection