<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    /**
     * Listagem de Franquias com Busca Multicorpos Integrada.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search'));

        // Inicializa a query carregando a relação de estoque local de produtos
        $query = Store::with('products');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                // Busca por nome, cidade, estado ou telefone da filial
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('state', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $stores = $query->orderBy('name', 'asc')->get();

        return view('stores.index', compact('stores', 'search'));
    }

    /**
     * Tela de Cadastro Manual de Franquias.
     */
    public function create()
    {
        return view('stores.create');
    }

    /**
     * Salva a Nova Franquia no Banco de Dados.
     * Método consumido tanto pelo front quanto pelas Tools de Escrita da IA.
     */
    public function store(Request $request)
    {
        // Validação Estrita do Laravel (Consumida e respeitada também pelas Tools)
        $request->validate([
            'name'  => 'required|string|max:255',
            'city'  => 'required|string|max:255',
            'state' => 'required|string|size:2', // UF com exatamente 2 letras
            'phone' => 'nullable|string|max:50',
        ]);

        $store = Store::create([
            'name'  => $request->input('name'),
            'city'  => $request->input('city'),
            'state' => strtoupper($request->input('state')),
            'phone' => $request->input('phone'),
        ]);

        // Se for uma requisição AJAX vinda da IA local, retorna JSON de sucesso puro
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['status' => 'success', 'store_id' => $store->id]);
        }

        return redirect()->route('stores.index')->with('success', 'Nova franquia regional cadastrada com sucesso!');
    }

    /**
     * Tela de Edição Manual de Franquias.
     */
    public function edit($id)
    {
        $store = Store::findOrFail($id);
        return view('stores.edit', compact('store'));
    }

    /**
     * Atualiza os dados cadastrais da loja física.
     * Método consumido tanto pelo front quanto pelas Tools de Edição da IA.
     */
    public function update(Request $request, $id)
    {
        $store = Store::findOrFail($id);

        $request->validate([
            'name'  => 'required|string|max:255',
            'city'  => 'required|string|max:255',
            'state' => 'required|string|size:2',
            'phone' => 'nullable|string|max:50',
        ]);

        $store->update([
            'name'  => $request->input('name'),
            'city'  => $request->input('city'),
            'state' => strtoupper($request->input('state')),
            'phone' => $request->input('phone'),
        ]);

        // Retorno JSON de sucesso para a Tool de Edição da IA
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'Franquia atualizada com sucesso.']);
        }

        return redirect()->route('stores.index')->with('success', 'Dados cadastrais da franquia atualizados!');
    }

    /**
     * Remove a franquia do sistema e limpa as amarrações em cascata no MySQL.
     */
    public function destroy($id)
    {
        $store = Store::findOrFail($id);
        
        // Cuidará da remoção na tabela store_products e store_sales de forma automática pelo cascade
        $store->delete();

        return redirect()->route('stores.index')->with('success', 'Franquia removida do mapa de distribuição regional!');
    }
}