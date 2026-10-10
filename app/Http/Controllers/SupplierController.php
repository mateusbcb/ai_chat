<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    // Listagem de Fornecedores
    public function index()
    {
        $suppliers = Supplier::with('products')->orderBy('name', 'asc')->get();
        return view('suppliers.index', compact('suppliers'));
    }

    // Tela de Criação
    public function create()
    {
        return view('suppliers.create');
    }

    // Salvar no Banco
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max=255',
            'cnpj' => 'required|string|unique:suppliers,cnpj',
            'contact_email' => 'required|email|max=255',
        ]);

        Supplier::create([
            'name' => $request->input('name'),
            'cnpj' => $request->input('cnpj'),
            'contact_email' => $request->input('contact_email'),
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Fornecedor homologado com sucesso!');
    }

    // Tela de Edição
    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('suppliers.edit', compact('supplier'));
    }

    // Atualizar no Banco
    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max=255',
            'cnpj' => 'required|string|unique:suppliers,cnpj,' . $supplier->id,
            'contact_email' => 'required|email|max=255',
        ]);

        $supplier->update([
            'name' => $request->input('name'),
            'cnpj' => $request->input('cnpj'),
            'contact_email' => $request->input('contact_email'),
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Dados do fornecedor atualizados!');
    }

    // Deletar Fornecedor
    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete(); // Remove os vínculos Muitos para Muitos de tabelas pivô de forma automática devido ao cascade

        return redirect()->route('suppliers.index')->with('success', 'Fornecedor removido do sistema!');
    }
}
