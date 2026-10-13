<?php

namespace App\Http\Controllers;

use App\Models\StoreSale;
use App\Models\Store;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreSaleController extends Controller
{
    /**
     * Listagem do Histórico Fiscal de Vendas com Busca Multicorpos.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search'));

        // Carrega os dados com os relacionamentos indexados da Loja e do Produto
        $query = StoreSale::with(['store', 'product']);

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                // 1. Filtra pelo status da movimentação (vendido, devolvido, quebra)
                $q->where('status', 'like', "%{$search}%");

                // 2. Filtra pelo Nome ou Cidade da Franquia emissora
                $q->orWhereHas('store', function($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                             ->where('city', 'like', "%{$search}%");
                });

                // 3. Filtra pelo Nome ou Código SKU do produto movimentado
                $q->orWhereHas('product', function($subQuery) use ($search) {
                    $subQuery->where('title', 'like', "%{$search}%")
                             ->orWhere('sku', 'like', "%{$search}%");
                });
            });
        }

        $sales = $query->orderBy('created_at', 'desc')->get();

        return view('sales.index', compact('sales', 'search'));
    }

    /**
     * Tela de Emissão/Lançamento Manual de Cupons e Perdas.
     */
    public function create()
    {
        $stores = Store::all();
        $products = Product::all();
        return view('sales.create', compact('stores', 'products'));
    }

    /**
     * Registra um novo Cupom Fiscal, Devolução ou Quebra física no banco.
     * Alinhado com o Proxy Controller Pattern para consumo automatizado via IA.
     */
    public function store(Request $request)
    {
        // Validação Estrita de Integridade do Movimento
        $request->validate([
            'store_id'   => 'required|exists:stores,id',
            'product_id' => 'required|exists:products,id',
            'price_sale' => 'required|numeric|min:0',
            'quantity'   => 'required|integer|min:1',
            'status'     => 'required|string|in:vendido,devolvido,quebra',
        ]);

        DB::beginTransaction();

        try {
            // 1. Lança o registro de movimentação/auditoria de caixa
            $sale = StoreSale::create([
                'store_id'   => $request->input('store_id'),
                'product_id' => $request->input('product_id'),
                'price_sale' => $request->input('price_sale'),
                'quantity'   => $request->input('quantity'),
                'status'     => $request->input('status'),
            ]);

            // 2. DEDUÇÃO AUTOMÁTICA DE INVENTÁRIO (Garantia de Sincronia de Estoque)
            // Se o produto foi "vendido" ou sofreu "quebra/perda", subtrai do estoque da loja
            $pivotQuery = DB::table('store_products')
                ->where('store_id', $request->input('store_id'))
                ->where('product_id', $request->input('product_id'));

            if ($request->input('status') === 'vendido' || $request->input('status') === 'quebra') {
                $pivotQuery->decrement('quantity', (int)$request->input('quantity'));
            } 
            // Se o status for uma "devolução", o item retorna fisicamente para as prateleiras locais
            elseif ($request->input('status') === 'devolvido') {
                $pivotQuery->increment('quantity', (int)$request->input('quantity'));
            }

            DB::commit();

            // Retorno JSON limpo para as futuras ferramentas (Tools) da Inteligência Artificial
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status'  => 'success',
                    'sale_id' => $sale->id,
                    'message' => 'Movimentação de caixa registrada e estoque local sincronizado.'
                ]);
            }

            return redirect()->route('sales.index')->with('success', 'Movimentação lançada no caixa com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }
            throw $e;
        }
    }

    /**
     * Remove ou anula um registro de venda do histórico fiscal.
     */
    public function destroy($id)
    {
        $sale = StoreSale::findOrFail($id);
        $sale->delete();

        return redirect()->route('sales.index')->with('success', 'Registro anulado e removido do histórico de auditoria!');
    }
}