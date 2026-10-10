@extends('layouts.app')

@section('content')
<!-- Cabeçalho do CRUD de Lojas -->
<div class="bg-slate-800 border-b border-slate-700 p-6 px-8 flex justify-between items-center flex-shrink-0">
    <div>
        <h2 class="font-bold text-xl text-white tracking-wide">🏢 Rede de Franquias / Lojas</h2>
        <p class="text-xs text-slate-400 mt-0.5">Gerenciamento de filiais e distribuição regional de estoque</p>
    </div>
    <a href="{{ route('stores.create') }}" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs py-2.5 px-4 rounded-xl shadow-md transition-all flex items-center gap-2 cursor-pointer">
        + Cadastrar Franquia
    </a>
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
                        <th class="p-4 text-center">Variedade de Produtos</th>
                        <th class="p-4 text-center">Estoque Total Acumulado</th>
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
                            <!-- Variedade de Modelos únicos (Count da Relação) -->
                            <td class="p-4 text-center font-semibold text-slate-300">
                                {{ $store->products->count() }} itens distintos
                            </td>
                            <!-- Volume total somando o stock_quantity da tabela pivô -->
                            <td class="p-4 text-center font-bold text-blue-400 text-sm">
                                {{ number_format($store->products->sum('pivot.stock_quantity'), 0, ',', '.') }} un
                            </td>
                            <!-- Ações -->
                            <td class="p-4 pr-6 text-right">
                                <div class="flex justify-end gap-2 items-center">
                                    <a href="{{ route('stores.edit', $store->id) }}" class="text-slate-400 hover:text-blue-400 font-medium transition-all px-2 py-1 border border-slate-700 hover:border-blue-500/20 rounded-md bg-slate-900/30">Editar</a>
                                    <form action="{{ route('stores.destroy', $store->id) }}" method="POST" onsubmit="return confirm('Deseja realmente remover esta franquia? Isso limpará os registros de estoque vinculados a ela.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-slate-400 hover:text-rose-400 font-medium transition-all px-2 py-1 border border-slate-700 hover:border-rose-500/20 rounded-md bg-slate-900/30 cursor-pointer">Excluir</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500 italic">Nenhuma franquia localizada no mapeamento de rede.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection