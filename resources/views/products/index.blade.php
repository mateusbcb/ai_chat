@extends('layouts.app')

@section('content')
<!-- Cabeçalho do CRUD com Barra de Busca Integrada -->
<div class="bg-slate-800 border-b border-slate-700 p-6 px-8 flex flex-col md:flex-row gap-4 justify-between items-start md:items-center flex-shrink-0">
    <div>
        <h2 class="font-bold text-xl text-white tracking-wide">📦 Catálogo & Estoque Central (CD)</h2>
        <p class="text-xs text-slate-400 mt-0.5">Gerenciamento de inventário da Matriz e rastreabilidade de lotes</p>
    </div>
    
    <!-- Form de Busca Multicorpos e Botão Cadastrar -->
    <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto items-stretch sm:items-center">
        <form action="{{ route('products.index') }}" method="GET" class="flex items-center bg-slate-900 border border-slate-700 rounded-xl px-3 py-1.5 focus-within:border-blue-500 transition-all w-full sm:w-80">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar por produto, SKU, cor, tamanho ou filial..." class="bg-transparent text-slate-200 placeholder-slate-500 text-xs focus:outline-none w-full pr-2">
            
            @if(!empty($search))
                <a href="{{ route('products.index') }}" class="text-slate-500 hover:text-slate-300 mr-2 text-sm" title="Limpar busca">✕</a>
            @endif
            
            <button type="submit" class="text-slate-400 hover:text-white cursor-pointer">
                <svg xmlns="http://w3.org" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.603 10.601z" />
                </svg>
            </button>
        </form>

        <a href="{{ route('products.create') }}" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs py-2.5 px-4 rounded-xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer whitespace-nowrap">
            + Cadastrar Produto
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
                        <th class="p-4 pl-6">Especificações do Item</th>
                        <th class="p-4">Código SKU</th>
                        <th class="p-4">Preço de Custo (CD)</th>
                        <th class="p-4 text-center">Estoque CD Matriz</th>
                        <th class="p-4">Status CD</th>
                        <th class="p-4">Distribuição Regional (Lojas)</th>
                        <th class="p-4 pr-6 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50 text-xs text-slate-300">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-700/20 transition-all">
                            <!-- Bloco de Especificações -->
                            <td class="p-4 pl-6 max-w-xs">
                                <div class="font-bold text-white text-sm">{{ $product->title }}</div>
                                <div class="flex items-center gap-2 mt-1 text-[10px] font-medium text-slate-400">
                                    <span class="bg-slate-900 px-1.5 py-0.5 rounded border border-slate-700">🎨 {{ $product->color ?? 'N/I' }}</span>
                                    <span class="bg-slate-900 px-1.5 py-0.5 rounded border border-slate-700">📏 Tam: {{ $product->size ?? 'N/I' }}</span>
                                    <span class="bg-slate-900 px-1.5 py-0.5 rounded border border-slate-700">⚖️ {{ $product->weight ? $product->weight . ' kg' : 'N/I' }}</span>
                                </div>
                            </td>
                            
                            <!-- SKU -->
                            <td class="p-4 font-mono text-slate-400 font-semibold">{{ $product->sku }}</td>
                            
                            <!-- Preço Base -->
                            <td class="p-4 font-semibold text-white">R$ {{ number_format($product->price_base, 2, ',', '.') }}</td>
                            
                            <!-- Quantidade de Estoque no CD -->
                            <td class="p-4 text-center font-bold text-base {{ $product->cd_stock_quantity <= 150 ? 'text-amber-400' : 'text-slate-100' }}">
                                {{ number_format($product->cd_stock_quantity, 0, ',', '.') }} un
                            </td>
                            
                            <!-- Status do CD -->
                            <td class="p-4">
                                @if($product->status === 'disponivel')
                                    <span class="bg-emerald-950/60 border border-emerald-500/30 text-emerald-400 px-2 py-0.5 rounded text-[10px] font-bold uppercase">Disponível</span>
                                @elseif($product->status === 'em_transito')
                                    <span class="bg-blue-950/60 border border-blue-500/30 text-blue-400 px-2 py-0.5 rounded text-[10px] font-bold uppercase">Em Trânsito</span>
                                @else
                                    <span class="bg-rose-950/60 border border-rose-500/30 text-rose-400 px-2 py-0.5 rounded text-[10px] font-bold uppercase">Esgotado</span>
                                @endif
                            </td>
                            
                            <!-- Estoques Locais das Franquias (Muitos para Muitos com Pivot) -->
                            <td class="p-4 max-w-xs">
                                <div class="space-y-1.5 py-1">
                                    @forelse($product->stores as $store)
                                        <div class="text-[11px] flex items-center justify-between gap-4 text-slate-400 border-b border-slate-700/30 pb-0.5 last:border-0">
                                            <span class="truncate" title="{{ $store->name }}">{{ $store->city }}:</span>
                                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                                <span class="font-bold text-slate-200">{{ $store->pivot->quantity }} un</span>
                                                @if($store->pivot->status === 'a_venda')
                                                    <span class="text-[9px] text-emerald-400 font-bold bg-emerald-950/40 px-1 rounded border border-emerald-500/20">À Venda</span>
                                                @elseif($store->pivot->status === 'em_transito')
                                                    <span class="text-[9px] text-blue-400 font-bold bg-blue-950/40 px-1 rounded border border-blue-500/20">Trânsito</span>
                                                @else
                                                    <span class="text-[9px] text-slate-400 font-bold bg-slate-900 px-1 rounded border border-slate-700">Dispo.</span>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <span class="text-slate-500 italic text-[11px]">Nenhuma remessa enviada</span>
                                    @endforelse
                                </div>
                            </td>
                            
                            <!-- Ações -->
                            <td class="p-4 pr-6 text-right">
                                <div class="flex justify-end gap-2 items-center">
                                    <a href="{{ route('products.edit', $product->id) }}" class="text-slate-400 hover:text-blue-400 font-medium transition-all px-2 py-1 border border-slate-700 hover:border-blue-500/20 rounded-md bg-slate-900/30">Editar</a>
                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Deseja realmente remover este produto? Isso apagará todos os lotes estocados nas lojas e o histórico de faturamento.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-slate-400 hover:text-rose-400 font-medium transition-all px-2 py-1 border border-slate-700 hover:border-rose-500/20 rounded-md bg-slate-900/30 cursor-pointer">Excluir</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500 italic">Nenhum registro de mercadoria localizado no Centro de Distribuição.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection