@extends('layouts.app')

@section('content')
<!-- Cabeçalho do Cadastro -->
<div class="bg-slate-800 border-b border-slate-700 p-6 px-8 flex justify-between items-center flex-shrink-0">
    <div>
        <h2 class="font-bold text-xl text-white tracking-wide">🏢 Nova Franquia / Loja</h2>
        <p class="text-xs text-slate-400 mt-0.5">Homologar uma nova filial regional na rede de distribuição</p>
    </div>
    <a href="{{ route('stores.index') }}" class="text-slate-400 hover:text-white font-medium text-xs border border-slate-700 hover:border-slate-500 px-4 py-2.5 rounded-xl transition-all bg-slate-900/50">
        Voltar para a lista
    </a>
</div>

<!-- Área do Formulário -->
<div class="flex-grow p-8 overflow-y-auto">
    <form action="{{ route('stores.store') }}" method="POST" class="max-w-2xl bg-slate-800 border border-slate-700 p-6 rounded-2xl shadow-xl space-y-6">
        @csrf

        <!-- Nome da Filial -->
        <div class="flex flex-col gap-1.5">
            <label for="name" class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Nome da Loja / Filial</label>
            <input type="text" name="name" id="name" class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-blue-500 transition-all" required placeholder="Ex: Nexus Express - Porto Alegre">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Cidade -->
            <div class="flex flex-col gap-1.5 md:col-span-2">
                <label for="city" class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Cidade</label>
                <input type="text" name="city" id="city" class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-blue-500 transition-all" required placeholder="Ex: Porto Alegre">
            </div>

            <!-- Estado (UF) -->
            <div class="flex flex-col gap-1.5">
                <label for="state" class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Estado (UF)</label>
                <input type="text" name="state" id="state" maxlength="2" class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 uppercase tracking-widest text-center focus:outline-none focus:border-blue-500 transition-all" required placeholder="RS">
            </div>
        </div>

        <!-- Botão de Envio -->
        <div class="pt-4 border-t border-slate-700/50 flex justify-end">
            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs py-3 px-6 rounded-xl shadow-md transition-all cursor-pointer">
                Salvar Nova Franquia
            </button>
        </div>
    </form>
</div>
@endsection