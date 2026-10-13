<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Listagem do Catálogo do CD com Busca Multicorpos Integrada.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search'));

        // Carrega o produto com o relacionamento do estoque local das lojas
        $query = Product::with('stores');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                // Busca por nome, SKU, cor ou tamanho no CD Central
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('color', 'like', "%{$search}%")
                  ->orWhere('size', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");

                // Busca se o produto está estocado em uma filial daquela cidade específica
                $q->orWhereHas('stores', function($subQuery) use ($search) {
                    $subQuery->where('city', 'like', "%{$search}%")
                             ->orWhere('name', 'like', "%{$search}%");
                });
            });
        }

        $products = $query->orderBy('created_at', 'desc')->get();

        return view('products.index', compact('products', 'search'));
    }

    /**
     * Tela de Cadastro Manual de Produtos.
     */
    public function create()
    {
        $stores = Store::all();
        return view('products.create', compact('stores'));
    }

    /**
     * Salva o Produto no CD Central e distribui o lote para as Franquias.
     * Método consumido tanto pelo front quanto pelas Tools da inteligência artificial.
     */
    public function store(Request $request)
    {
        // Validação Estrita do Laravel (Consumida e respeitada também pela IA)
        $request->validate([
            'title'             => 'required|string|max:255',
            'sku'               => 'required|string|unique:products,sku',
            'price_base'        => 'required|numeric|min:0',
            'cd_stock_quantity' => 'required|integer|min:0',
            'color'             => 'nullable|string|max:100',
            'size'              => 'nullable|string|max:50',
            'weight'            => 'nullable|numeric|min:0',
            'description'       => 'nullable|string',
            'status'            => 'required|string|in:disponivel,em_transito,esgotado',
        ]);

        // Executa a transação no banco para garantir consistência total
        DB::beginTransaction();

        try {
            // 1. Salva o produto na Matriz (Centro de Distribuição)
            $product = Product::create($request->only([
                'title', 'sku', 'price_base', 'cd_stock_quantity', 
                'color', 'size', 'weight', 'description', 'status'
            ]));

            // 2. Se o formulário ou a IA enviou filiais para distribuição imediata de lote
            if ($request->has('stores') && is_array($request->input('stores'))) {
                $storesData = [];
                foreach ($request->input('stores') as $storeId) {
                    // Calcula a margem de lucro sugerida regional padrão (+50%)
                    $precoVendaSugerido = $product->price_base * 1.50;

                    $storesData[$storeId] = [
                        'quantity'             => rand(10, 50), // Envia uma remessa inicial simulada de caminhão
                        'price_sale_suggested' => round($precoVendaSugerido, 2),
                        'status'               => 'em_transito', // O lote inicia viajando em trânsito
                        'created_at'           => now(),
                        'updated_at'           => now()
                    ];
                }
                // Alimenta a tabela pivô de estoque da loja
                $product->stores()->attach($storesData);
            }

            DB::commit();

            // Se for uma requisição AJAX vinda da Tool de IA, retorna JSON puro
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'success', 'product_id' => $product->id]);
            }

            return redirect()->route('products.index')->with('success', 'Produto registrado e estocado no CD com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Tela de Edição Manual do Produto.
     */
    public function edit($id)
    {
        $product = Product::with('stores')->findOrFail($id);
        $stores = Store::all();
        return view('products.edit', compact('product', 'stores'));
    }

    /**
     * Atualiza os dados do produto no Centro de Distribuição Central.
     * Método consumido tanto pelo front quanto pelas Tools de Edição da IA.
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // Validação Estrita de Atualização (Respeitada também pelas Tools)
        $request->validate([
            'title'             => 'required|string|max:255',
            'sku'               => 'required|string|unique:products,sku,' . $product->id,
            'price_base'        => 'required|numeric|min:0',
            'cd_stock_quantity' => 'required|integer|min:0',
            'color'             => 'nullable|string|max:100',
            'size'              => 'nullable|string|max:50',
            'weight'            => 'nullable|numeric|min:0',
            'description'       => 'nullable|string',
            'status'            => 'required|string|in:disponivel,em_transito,esgotado',
        ]);

        DB::beginTransaction();

        try {
            // Atualiza os dados do nó central do CD
            $product->update($request->only([
                'title', 'sku', 'price_base', 'cd_stock_quantity', 
                'color', 'size', 'weight', 'description', 'status'
            ]));

            // Se o formulário enviou um array de filiais, sincroniza as lojas mantendo a integridade
            if ($request->has('stores') && is_array($request->input('stores'))) {
                $storesData = [];
                foreach ($request->input('stores') as $storeId) {
                    // Mantém os dados antigos do pivot se já existirem, ou cria uma nova remessa com +50%
                    $existing = $product->stores()->where('store_id', $storeId)->first();
                    $precoVendaSugerido = $existing ? $existing->pivot->price_sale_suggested : ($product->price_base * 1.50);
                    $quantidadeAtual = $existing ? $existing->pivot->quantity : rand(10, 50);
                    $statusAtual = $existing ? $existing->pivot->status : 'em_transito';

                    $storesData[$storeId] = [
                        'quantity'             => $quantidadeAtual,
                        'price_sale_suggested' => round($precoVendaSugerido, 2),
                        'status'               => $statusAtual,
                        'updated_at'           => now()
                    ];
                }
                $product->stores()->sync($storesData);
            }

            DB::commit();

            // Retorno JSON de sucesso puro para a Tool de IA
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'success', 'message' => 'Produto atualizado com sucesso.']);
            }

            return redirect()->route('products.index')->with('success', 'Registro de inventário do CD atualizado!');

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Remove o produto do catálogo e limpa todas as tabelas pivô associadas.
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
        // O Laravel cuidará da remoção física nas tabelas vinculadas graças ao onDelete('cascade') das migrations
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produto removido do catálogo e desvinculado das lojas!');
    }
}