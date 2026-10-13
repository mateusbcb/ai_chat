@extends('layouts.app')

@section('content')
<!-- Cabeçalho -->
<div class="bg-slate-800 border-b border-slate-700 p-6 px-8 flex justify-between items-center flex-shrink-0">
    <div>
        <h2 class="font-bold text-xl text-white tracking-wide">＋ Dar Entrada de Mercadoria (CD)</h2>
        <p class="text-xs text-slate-400 mt-0.5">Registrar novo item no Centro de Distribuição Central e preparar logística</p>
    </div>
    <a href="{{ route('products.index') }}" class="text-slate-400 hover:text-white font-medium text-xs border border-slate-700 hover:border-slate-500 px-4 py-2.5 rounded-xl transition-all bg-slate-900/50">
        Voltar para o Catálogo
    </a>
</div>

<!-- Área do Formulário -->
<div class="flex-grow p-8 overflow-y-auto">
    <form action="{{ route('products.store') }}" method="POST" class="max-w-4xl bg-slate-800 border border-slate-700 p-6 rounded-2xl shadow-xl space-y-6">
        @csrf

        <!-- Seção 1: Identificação Básica -->
        <div>
            <h3 class="text-sm font-bold text-blue-400 uppercase tracking-wider mb-4">1. Identificação do Produto</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Título -->
                <div class="flex flex-col gap-1.5 md:col-span-2">
                    <label for="title" class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Nome do Produto</label>
                    <input type="text" name="title" id="title" class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-blue-500 transition-all" required placeholder="Ex: Jaqueta Corta Vento Impermeável">
                </div>

                <!-- SKU -->
                <div class="flex flex-col gap-1.5">
                    <label for="sku" class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Código SKU Único</label>
                    <input type="text" name="sku" id="sku" class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 font-mono focus:outline-none focus:border-blue-500 transition-all" required placeholder="Ex: NEX-WMS-JKT05">
                </div>
            </div>
        </div>

        <!-- Seção 2: Atributos Físicos e Custos -->
        <div class="pt-4 border-t border-slate-700/50">
            <h3 class="text-sm font-bold text-blue-400 uppercase tracking-wider mb-4">2. Atributos Físicos & Custos</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                <!-- Preço Base -->
                <div class="flex flex-col gap-1.5">
                    <label for="price_base" class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Preço de Custo CD (R$)</label>
                    <input type="number" step="0.01" name="price_base" id="price_base" class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-blue-500 transition-all" required placeholder="0.00">
                </div>

                <!-- Cor -->
                <div class="flex flex-col gap-1.5">
                    <label for="color" class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Cor / Pigmentação</label>
                    <input type="text" name="color" id="color" class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-blue-500 transition-all" placeholder="Ex: Preto Matte">
                </div>

                <!-- Tamanho -->
                <div class="flex flex-col gap-1.5">
                    <label for="size" class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Grade / Tamanho</label>
                    <input type="text" name="size" id="size" class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-blue-500 transition-all" placeholder="Ex: G, GG, 42">
                </div>

                <!-- Peso -->
                <div class="flex flex-col gap-1.5">
                    <label for="weight" class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Peso Líquido (KG)</label>
                    <input type="number" step="0.01" name="weight" id="weight" class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-blue-500 transition-all" placeholder="0.00">
                </div>
            </div>
        </div>

        <!-- Seção 3: Controle de Inventário da Matriz -->
        <div class="pt-4 border-t border-slate-700/50">
            <h3 class="text-sm font-bold text-blue-400 uppercase tracking-wider mb-4">3. Controle de Inventário da Matriz</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Quantidade Inicial no CD -->
                <div class="flex flex-col gap-1.5">
                    <label for="cd_stock_quantity" class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Estoque Pulmão Inicial (CD)</label>
                    <input type="number" name="cd_stock_quantity" id="cd_stock_quantity" class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-blue-500 transition-all" required placeholder="Ex: 500">
                </div>

                <!-- Status Inicial no CD -->
                <div class="flex flex-col gap-1.5">
                    <label for="status" class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Situação Logística (CD)</label>
                    <select name="status" id="status" class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-blue-500 transition-all cursor-pointer">
                        <option value="disponivel" selected>📦 DISPONÍVEL NAS DOCAS</option>
                        <option value="em_transito">🚚 REMESSA EM TRÂNSITO</option>
                        <option value="esgotado">❌ INDISPONÍVEL / ESGOTADO</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Descrição -->
        <div class="flex flex-col gap-1.5">
            <label for="description" class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Observações / Detalhes de Entrada</label>
            <textarea name="description" id="description" rows="3" class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-blue-500 transition-all resize-none" placeholder="Insira notas sobre o lote ou especificações alfandegárias/fiscais se necessário..."></textarea>
        </div>

        <!-- Seção 4: Distribuição Regional Remota -->
        <div class="pt-4 border-t border-slate-700/50 flex flex-col gap-2">
            <h3 class="text-sm font-bold text-blue-400 uppercase tracking-wider">4. Distribuição Regional (Franquias)</h3>
            <p class="text-[11px] text-slate-400 mb-2">Marque quais filiais receberão frações automáticas de lote (as unidades serão faturadas com +50% de margem e marcadas como 'Em Trânsito').</p>
            
            <div class="bg-slate-900 border border-slate-700 rounded-xl p-4 max-h-48 overflow-y-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                @foreach($stores as $store)
                    <label class="flex items-center gap-3 text-xs text-slate-300 cursor-pointer select-none bg-slate-800/40 p-2.5 rounded-lg border border-slate-700/50 hover:border-slate-600 transition-all">
                        <input type="checkbox" name="stores" value="{{ $store->id }}" class="w-4 h-4 rounded border-slate-700 bg-slate-800 text-blue-600 focus:ring-0 cursor-pointer">
                        <div class="truncate">
                            <span class="font-bold text-white block truncate">{{ $store->name }}</span>
                            <span class="text-[10px] text-slate-500 font-medium">{{ $store->city }} - {{ $store->state }}</span>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Botão Salvar -->
        <div class="pt-4 border-t border-slate-700/50 flex justify-end">
            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs py-3 px-6 rounded-xl shadow-md transition-all cursor-pointer">
                Efetivar Entrada no CD & Despachar Lotes
            </button>
        </div>
    </form>
</div>
@endsection