@extends('layouts.app')

@section('content')
<!-- Cabeçalho -->
<div class="bg-slate-800 border-b border-slate-700 p-6 px-8 flex justify-between items-center flex-shrink-0">
    <div>
        <h2 class="font-bold text-xl text-white tracking-wide">＋ Novo Produto</h2>
        <p class="text-xs text-slate-400 mt-0.5">Adicionar um novo item à rede de distribuição</p>
    </div>
    <a href="{{ route('products.index') }}" class="text-slate-400 hover:text-white font-medium text-xs border border-slate-700 hover:border-slate-500 px-4 py-2.5 rounded-xl transition-all bg-slate-900/50">
        Voltar para a lista
    </a>
</div>

<!-- Área do Formulário -->
<div class="flex-grow p-8 overflow-y-auto">
    <form action="{{ route('products.store') }}" method="POST" class="max-w-3xl bg-slate-800 border border-slate-700 p-6 rounded-2xl shadow-xl space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Título -->
            <div class="flex flex-col gap-1.5">
                <label for="title" class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Nome do Produto</label>
                <input type="text" name="title" id="title" class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-blue-500 transition-all" required placeholder="Ex: Camiseta Polo Azul">
            </div>

            <!-- SKU -->
            <div class="flex flex-col gap-1.5">
                <label for="sku" class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Código SKU</label>
                <input type="text" name="sku" id="sku" class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 font-mono focus:outline-none focus:border-blue-500 transition-all" required placeholder="Ex: NEX-PROD-9981">
            </div>

            <!-- Preço -->
            <div class="flex flex-col gap-1.5">
                <label for="price" class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Preço Sugerido (R$)</label>
                <input type="number" step="0.01" name="price" id="price" class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-blue-500 transition-all" required placeholder="0.00">
            </div>
        </div>

        <!-- Descrição -->
        <div class="flex flex-col gap-1.5">
            <label for="description" class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Descrição / Especificações</label>
            <textarea name="description" id="description" rows="3" class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-blue-500 transition-all resize-none" placeholder="Detalhes técnicos sobre as dimensões, tecidos ou componentes..."></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-700/50">
            <!-- Seleção de Fornecedores -->
            <div class="flex flex-col gap-2">
                <label class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Vincular Fornecedores</label>
                <div class="bg-slate-900 border border-slate-700 rounded-xl p-4 max-h-48 overflow-y-auto space-y-2">
                    @foreach($suppliers as $supplier)
                        <label class="flex items-center gap-3 text-xs text-slate-300 cursor-pointer select-none">
                            <input type="checkbox" name="suppliers[]" value="{{ $supplier->id }}" class="w-4 h-4 rounded border-slate-700 bg-slate-800 text-blue-600 focus:ring-0">
                            <span>{{ $supplier->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Seleção de Lojas -->
            <div class="flex flex-col gap-2">
                <label class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Distribuir para Franquias</label>
                <div class="bg-slate-900 border border-slate-700 rounded-xl p-4 max-h-48 overflow-y-auto space-y-2">
                    @foreach($stores as $store)
                        <label class="flex items-center gap-3 text-xs text-slate-300 cursor-pointer select-none">
                            <input type="checkbox" name="stores[]" value="{{ $store->id }}" class="w-4 h-4 rounded border-slate-700 bg-slate-800 text-blue-600 focus:ring-0">
                            <span>{{ $store->name }} ({{ $store->city }})</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Botão Salvar -->
        <div class="pt-4 flex justify-end">
            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs py-3 px-6 rounded-xl shadow-md transition-all cursor-pointer">
                Salvar Produto no Catálogo
            </button>
        </div>
    </form>
</div>
@endsection