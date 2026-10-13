@extends('layouts.app')

@section('content')
<!-- Cabeçalho do Cadastro -->
<div class="bg-slate-800 border-b border-slate-700 p-6 px-8 flex justify-between items-center flex-shrink-0">
    <div>
        <h2 class="font-bold text-xl text-white tracking-wide">📊 Registrar Movimentação de Caixa (PDV)</h2>
        <p class="text-xs text-slate-400 mt-0.5">Lançar cupons fiscais de venda, estornos de devolução ou perdas físicas</p>
    </div>
    <a href="{{ route('sales.index') }}" class="text-slate-400 hover:text-white font-medium text-xs border border-slate-700 hover:border-slate-500 px-4 py-2.5 rounded-xl transition-all bg-slate-900/50">
        Voltar para a Auditoria
    </a>
</div>

<!-- Área do Formulário -->
<div class="flex-grow p-8 overflow-y-auto">
    <form action="{{ route('sales.store') }}" method="POST" class="max-w-2xl bg-slate-800 border border-slate-700 p-6 rounded-2xl shadow-xl space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Seleção da Filial Emissora -->
            <div class="flex flex-col gap-1.5">
                <label for="store_id" class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Filial / Franquia Emissora</label>
                <select name="store_id" id="store_id" class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-blue-500 transition-all cursor-pointer" required>
                    <option value="" disabled selected>Selecione a Loja...</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}">{{ $store->name }} ({{ $store->city }}-{{ $store->state }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Seleção do Produto / SKU -->
            <div class="flex flex-col gap-1.5">
                <label for="product_id" class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Item Movimentado (SKU)</label>
                <select name="product_id" id="product_id" class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-blue-500 transition-all cursor-pointer" required>
                    <option value="" disabled selected>Selecione o Produto...</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->title }} [SKU: {{ $product->sku }}]</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <!-- Tipo de Movimento / Status -->
            <div class="flex flex-col gap-1.5 sm:col-span-1">
                <label for="status" class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Tipo de Movimento</label>
                <select name="status" id="status" class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-blue-500 transition-all cursor-pointer" required onchange="ajustarCampoPreco(this.value)">
                    <option value="vendido" selected>💰 VENDA CONFIRMADA</option>
                    <option value="devolvido">🔄 DEVOLUÇÃO / ESTORNO</option>
                    <option value="quebra">⚠️ QUEBRA / PERDA FÍSICA</option>
                </select>
            </div>

            <!-- Preço de Venda Real -->
            <div class="flex flex-col gap-1.5">
                <label for="price_sale" class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Preço Praticado (R$)</label>
                <input type="number" step="0.01" min="0" name="price_sale" id="price_sale" class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-blue-500 transition-all" required placeholder="0.00">
            </div>

            <!-- Quantidade Movimentada -->
            <div class="flex flex-col gap-1.5">
                <label for="quantity" class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Quantidade</label>
                <input type="number" min="1" name="quantity" id="quantity" class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-blue-500 transition-all" required placeholder="Ex: 1">
            </div>
        </div>

        <!-- Botão de Envio -->
        <div class="pt-4 border-t border-slate-700/50 flex justify-end">
            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs py-3 px-6 rounded-xl shadow-md transition-all cursor-pointer">
                Lançar e Sincronizar Estoque
            </button>
        </div>
    </form>
</div>

<script>
    /**
     * Zera automaticamente o campo de preço se o operador lançar uma quebra/perda, 
     * já que quebras representam perda total financeira do estoque físico.
     */
    function ajustarCampoPreco(status) {
        const campoPreco = document.getElementById('price_sale');
        if (status === 'quebra') {
            campoPreco.value = '0.00';
            campoPreco.readOnly = true;
            campoPreco.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            campoPreco.readOnly = false;
            campoPreco.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }
</script>
@endsection