@extends('layouts.app')

@section('content')
<!-- Cabeçalho do CRUD de Fornecedores -->
<div class="bg-slate-800 border-b border-slate-700 p-6 px-8 flex justify-between items-center flex-shrink-0">
    <div>
        <h2 class="font-bold text-xl text-white tracking-wide">🚚 Homologação de Fornecedores</h2>
        <p class="text-xs text-slate-400 mt-0.5">Mapeamento de parceiros comerciais e fabricantes cadastrados</p>
    </div>
    <a href="{{ route('suppliers.create') }}" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs py-2.5 px-4 rounded-xl shadow-md transition-all flex items-center gap-2 cursor-pointer">
        + Cadastrar Fornecedor
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
                        <th class="p-4 pl-6">Razão Social / Nome</th>
                        <th class="p-4">Documento CNPJ</th>
                        <th class="p-4">E-mail Comercial</th>
                        <th class="p-4 text-center">Mix de Distribuição</th>
                        <th class="p-4 pr-6 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50 text-xs text-slate-300">
                    @forelse($suppliers as $supplier)
                        <tr class="hover:bg-slate-700/20 transition-all">
                            <!-- Nome da Empresa -->
                            <td class="p-4 pl-6">
                                <div class="font-bold text-white text-sm">{{ $supplier->name }}</div>
                            </td>
                            <!-- CNPJ -->
                            <td class="p-4 font-mono text-slate-400 font-medium">
                                {{ $supplier->cnpj }}
                            </td>
                            <!-- E-mail de Contato -->
                            <td class="p-4 text-slate-300 font-medium">
                                {{ $supplier->contact_email }}
                            </td>
                            <!-- Volume de Produtos Distintos Fornecidos -->
                            <td class="p-4 text-center font-bold text-emerald-400 text-sm">
                                {{ $supplier->products->count() }} produtos
                            </td>
                            <!-- Ações -->
                            <td class="p-4 pr-6 text-right">
                                <div class="flex justify-end gap-2 items-center">
                                    <a href="{{ route('suppliers.edit', $supplier->id) }}" class="text-slate-400 hover:text-blue-400 font-medium transition-all px-2 py-1 border border-slate-700 hover:border-blue-500/20 rounded-md bg-slate-900/30">Editar</a>
                                    <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" onsubmit="return confirm('Deseja realmente remover este fornecedor do sistema?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-slate-400 hover:text-rose-400 font-medium transition-all px-2 py-1 border border-slate-700 hover:border-rose-500/20 rounded-md bg-slate-900/30 cursor-pointer">Excluir</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500 italic">Nenhum parceiro ou fornecedor homologado no CRM.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection