<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Product;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    // Listagem de Lojas/Franquias
    public function index()
    {
        $stores = Store::with('products')->orderBy('name', 'asc')->get();
        return view('stores.index', compact('stores'));
    }

    // Tela de Criação
    public function create()
    {
        return view('stores.create');
    }

    // Salvar Nova Franquia
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max=255',
            'city' => 'required|string|max=255',
            'state' => 'required|string|size:2', // UF com 2 caracteres
        ]);

        Store::create([
            'name' => $request->input('name'),
            'city' => $request->input('city'),
            'state' => strtoupper($request->input('state')),
        ]);

        return redirect()->route('stores.index')->with('success', 'Franquia cadastrada com sucesso!');
    }

    // Tela de Edição
    public function edit($id)
    {
        $store = Store::findOrFail($id);
        return view('stores.edit', compact('store'));
    }

    // Atualizar no Banco
    public function update(Request $request, $id)
    {
        $store = Store::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max=255',
            'city' => 'required|string|max=255',
            'state' => 'required|string|size:2',
        ]);

        $store->update([
            'name' => $request->input('name'),
            'city' => $request->input('city'),
            'state' => strtoupper($request->input('state')),
        ]);

        return redirect()->route('stores.index')->with('success', 'Dados da franquia atualizados!');
    }

    // Remover Franquia
    public function destroy($id)
    {
        $store = Store::findOrFail($id);
        $store->delete(); // Remove os vínculos em cascata na tabela product_store automaticamente

        return redirect()->route('stores.index')->with('success', 'Franquia removida do sistema!');
    }
}
