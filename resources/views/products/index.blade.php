@extends('layouts.app')

@section('content')
<!-- Cabeçalho do CRUD -->
<div class="bg-slate-800 border-b border-slate-700 p-6 px-8 flex justify-between items-center flex-shrink-0">
    <div>
        <h2 class="font-bold text-xl text-white tracking-wide">📦 Gestão de Produtos</h2>
        <p class="text-xs text-slate-400 mt-0.5">Listagem centralizada do catálogo de mercadorias</p>
    </div>
    <a href="{{ route('products.create') }}" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs py-2.5 px-4 rounded-xl shadow-md transition-all flex items-center gap-2 cursor-pointer">
        + Cadastrar Produto
    </a>
</div>

<!-- Área da Tabela -->
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
                        <th class="p-4 pl-6">Produto</th>
                        <th class="p-4">SKU</th>
                        <th class="p-4">Preço sugerido</th>
                        <th class="p-4">Fornecedores</th>
                        <th class="p-4">Estoque por Loja</th>
                        <th class="p-4 pr-6 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50 text-xs text-slate-300">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-700/20 transition-all">
                            <!-- Título e Descrição -->
                            <td class="p-4 pl-6 max-w-xs">
                                <div class="font-bold text-white text-sm">{{ $product->title }}</div>
                                <div class="text-slate-400 mt-0.5 truncate">{{ $product->description ?? 'Sem descrição informada.' }}</div>
                            </td>
                            <!-- SKU -->
                            <td class="p-4 font-mono text-slate-400 font-medium">{{ $product->sku }}</td>
                            <!-- Preço -->
                            <td class="p-4 font-semibold text-white">R$ {{ number_format($product->price, 2, ',', '.') }}</td>
                            <!-- Fornecedores Vinculados (Muitos para Muitos) -->
                            <td class="p-4 max-w-xs">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($product->suppliers as $supplier)
                                        <span class="bg-slate-900 border border-slate-700 text-slate-300 px-2 py-0.5 rounded text-[10px]">{{ $supplier->name }}</span>
                                    @empty
                                        <span class="text-slate-500 italic">Nenhum</span>
                                    @endforelse
                                </div>
                            </td>
                            <!-- Lojas e Quantidades de Estoque (Muitos para Muitos com Pivot) -->
                            <td class="p-4 max-w-xs">
                                <div class="space-y-1">
                                    @forelse($product->stores as $store)
                                        <div class="text-[11px] flex justify-between gap-4 text-slate-400">
                                            <span>{{ $store->city }}:</span>
                                            <span class="font-bold text-slate-200">{{ $store->pivot->stock_quantity }} un</span>
                                        </div>
                                    @empty
                                        <span class="text-slate-500 italic">Sem estoque cadastrado</span>
                                    @endforelse
                                </div>
                            </td>
                            <!-- Botões de Ação -->
                            <td class="p-4 pr-6 text-right">
                                <div class="flex justify-end gap-2 items-center">
                                    <a href="{{ route('products.edit', $product->id) }}" class="text-slate-400 hover:text-blue-400 font-medium transition-all px-2 py-1 border border-slate-700 hover:border-blue-500/20 rounded-md bg-slate-900/30">Editar</a>
                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Deseja realmente excluir este produto?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-slate-400 hover:text-rose-400 font-medium transition-all px-2 py-1 border border-slate-700 hover:border-rose-500/20 rounded-md bg-slate-900/30 cursor-pointer">Excluir</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500 italic">Nenhum produto cadastrado no catálogo.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection