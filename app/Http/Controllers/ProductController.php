<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\Store;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Listagem de Produtos
    public function index()
    {
        $products = Product::with(['suppliers', 'stores'])->orderBy('created_at', 'desc')->get();
        return view('products.index', compact('products'));
    }

    // Tela de Criação
    public function create()
    {
        $suppliers = Supplier::all();
        $stores = Store::all();
        return view('products.create', compact('suppliers', 'stores'));
    }

    // Salvar no Banco
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max=255',
            'sku' => 'required|string|unique:products,sku',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $product = Product::create($request->only(['title', 'sku', 'price', 'description']));

        // Sincroniza os fornecedores selecionados
        if ($request->has('suppliers')) {
            $product->suppliers()->sync($request->input('suppliers'));
        }

        // Sincroniza as lojas selecionadas (com estoque padrão 10 para o teste)
        if ($request->has('stores')) {
            $storesData = [];
            foreach ($request->input('stores') as $storeId) {
                $storesData[$storeId] = ['stock_quantity' => rand(10, 100)];
            }
            $product->stores()->sync($storesData);
        }

        return redirect()->route('products.index')->with('success', 'Produto criado com sucesso!');
    }

    // Tela de Edição
    public function edit($id)
    {
        $product = Product::with(['suppliers', 'stores'])->findOrFail($id);
        $suppliers = Supplier::all();
        $stores = Store::all();
        return view('products.edit', compact('product', 'suppliers', 'stores'));
    }

    // Atualizar no Banco
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max=255',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $product->update($request->only(['title', 'sku', 'price', 'description']));

        // Atualiza os relacionamentos
        $product->suppliers()->sync($request->input('suppliers', []));
        
        $storesData = [];
        foreach ($request->input('stores', []) as $storeId) {
            $storesData[$storeId] = ['stock_quantity' => rand(10, 100)];
        }
        $product->stores()->sync($storesData);

        return redirect()->route('products.index')->with('success', 'Produto atualizado com sucesso!');
    }

    // Deletar Produto
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete(); // Os relacionamentos nas tabelas pivô somem automaticamente devido ao cascade da migration

        return redirect()->route('products.index')->with('success', 'Produto deletado com sucesso!');
    }
}
