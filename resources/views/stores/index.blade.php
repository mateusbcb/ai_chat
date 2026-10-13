@extends('layouts.app')

@section('content')
<!-- Cabeçalho do CRUD com Barra de Busca Integrada -->
<div class="bg-slate-800 border-b border-slate-700 p-6 px-8 flex flex-col md:flex-row gap-4 justify-between items-start md:items-center flex-shrink-0">
    <div>
        <h2 class="font-bold text-xl text-white tracking-wide">🏢 Hubs Regionais / Franquias</h2>
        <p class="text-xs text-slate-400 mt-0.5">Mapeamento de filiais e controle descentralizado de inventário de ponta</p>
    </div>
    
    <!-- Form de Busca Multicorpos e Botão Cadastrar -->
    <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto items-stretch sm:items-center">
        <form action="{{ route('stores.index') }}" method="GET" class="flex items-center bg-slate-900 border border-slate-700 rounded-xl px-3 py-1.5 focus-within:border-blue-500 transition-all w-full sm:w-80">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar por filial, cidade, estado ou telefone..." class="bg-transparent text-slate-200 placeholder-slate-500 text-xs focus:outline-none w-full pr-2">
            
            @if(!empty($search))
                <a href="{{ route('stores.index') }}" class="text-slate-500 hover:text-slate-300 mr-2 text-sm" title="Limpar busca">✕</a>
            @endif
            
            <button type="submit" class="text-slate-400 hover:text-white cursor-pointer">
                <svg xmlns="http://w3.org" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.603 10.601z" />
                </svg>
            </button>
        </form>

        <a href="{{ route('stores.create') }}" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs py-2.5 px-4 rounded-xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer whitespace-nowrap">
            + Cadastrar Franquia
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
                        <th class="p-4 pl-6">Nome da Filial</th>
                        <th class="p-4">Localização (Cidade/UF)</th>
                        <th class="p-4">Contato Telefônico</th>
                        <th class="p-4 text-center">Mix de SKUs Locais</th>
                        <th class="p-4 text-center">Volume Físico Estocado</th>
                        <th class="p-4 pr-6 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50 text-xs text-slate-300">
                    @forelse($stores as $store)
                        <tr class="hover:bg-slate-700/20 transition-all">
                            <!-- Nome da Loja -->
                            <td class="p-4 pl-6">
                                <div class="font-bold text-white text-sm">{{ $store->name }}</div>
                            </td>
                            
                            <!-- Localização -->
                            <td class="p-4">
                                <span class="text-slate-200 font-medium">{{ $store->city }}</span>
                                <span class="text-slate-500 font-bold ml-1 text-[11px] bg-slate-900 px-1.5 py-0.5 rounded border border-slate-700">{{ $store->state }}</span>
                            </td>
                            
                            <!-- Telefone de Contato -->
                            <td class="p-4 font-mono text-slate-400">
                                {{ $store->phone ?? 'Sem contato registrado' }}
                            </td>
                            
                            <!-- Variedade de SKUs Únicos na Loja -->
                            <td class="p-4 text-center font-semibold text-slate-300">
                                {{ $store->products->count() }} modelos
                            </td>
                            
                            <!-- Volume Total Somando a coluna quantity do pivot -->
                            <td class="p-4 text-center font-bold text-blue-400 text-sm">
                                {{ number_format($store->products->sum('pivot.quantity'), 0, ',', '.') }} un
                            </td>
                            
                            <!-- Ações -->
                            <td class="p-4 pr-6 text-right">
                                <div class="flex justify-end gap-2 items-center">
                                    <a href="{{ route('stores.edit', $store->id) }}" class="text-slate-400 hover:text-blue-400 font-medium transition-all px-2 py-1 border border-slate-700 hover:border-blue-500/20 rounded-md bg-slate-900/30">Editar</a>
                                    <form action="{{ route('stores.destroy', $store->id) }}" method="POST" onsubmit="return confirm('Deseja realmente remover esta franquia? Isso limpará os históricos locais de faturamento e os saldos de estoque correspondentes.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-slate-400 hover:text-rose-400 font-medium transition-all px-2 py-1 border border-slate-700 hover:border-rose-500/20 rounded-md bg-slate-900/30 cursor-pointer">Excluir</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500 italic">Nenhum hub regional localizado no mapa do sistema.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection