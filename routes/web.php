<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\StoreSaleController;
use Illuminate\Support\Facades\DB;

// Rota 1: Analisa se a IA precisa de ferramentas ou se é uma conversa comum
Route::post('/chat/send/{id}', [ChatController::class, 'sendMessage'])->name('chat.send');
// Rota 2: Executa o streaming de texto final na tela
Route::post('/chat/stream/{id}', [ChatController::class, 'streamResponse'])->name('chat.stream');

// Demais rotas de AI
Route::get('/chat/{id?}', [ChatController::class, 'index'])->name('chat.index');
Route::post('/chat/new', [ChatController::class, 'newSession'])->name('chat.new');
Route::post('/chat/clear/{id}', [ChatController::class, 'clearChat'])->name('chat.clear');

// ----

// Rota Raiz - Dashboard Logístico e Comercial do CD
Route::get('/', function () {
    // Coleta as métricas reais da nova malha de distribuição do WMS
    $totalProdutosCatalogo = \App\Models\Product::count();
    $totalLojasAtivas = \App\Models\Store::count();
    
    // Estoque total pulmão que ainda está fisicamente dentro do CD Matriz
    $estoqueCDCentral = \App\Models\Product::sum('cd_stock_quantity');
    
    // Estoque total descentralizado que já foi enviado para as filiais (Tabela Pivô)
    $estoqueTotalLojas = DB::table('store_products')->sum('quantity');

    // Faturamento bruto total das vendas confirmadas (Status: vendido)
    $faturamentoBruto = DB::table('store_sales')
        ->where('status', 'vendido')
        ->select(DB::raw('SUM(price_sale * quantity) as total'))
        ->first()->total ?? 0;

    return view('dashboard', compact(
        'totalProdutosCatalogo', 
        'totalLojasAtivas', 
        'estoqueCDCentral', 
        'estoqueTotalLojas', 
        'faturamentoBruto'
    ));
})->name('dashboard');

// Rotas do CRUD de Produtos (Já cadastradas)
Route::resource('products', ProductController::class);

// Registra as 7 rotas padrões para o CRUD de Lojas
Route::resource('stores', StoreController::class);

// Registra as rotas para o controle de Auditoria de Vendas e Quebras
Route::resource('sales', StoreSaleController::class);
