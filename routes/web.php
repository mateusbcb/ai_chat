<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\DB;

// Rotas do Chat IA Local (Atualizadas para o padrão com ID de aba)
Route::get('/chat/{id?}', [ChatController::class, 'index'])->name('chat.index');
Route::post('/chat/new', [ChatController::class, 'newSession'])->name('chat.new');
Route::post('/chat/send/{id}', [ChatController::class, 'sendMessage'])->name('chat.send');
Route::post('/chat/clear/{id}', [ChatController::class, 'clearChat'])->name('chat.clear');

// ----

// Rota Raiz - Dashboard do CRM
Route::get('/', function () {
    // Coleta métricas rápidas do banco real para o Dashboard fakes
    $totalProdutos = \App\Models\Product::count();
    $totalFornecedores = \App\Models\Supplier::count();
    $totalLojas = \App\Models\Store::count();
    $totalEstoque = DB::table('product_store')->sum('stock_quantity');

    return view('dashboard', compact('totalProdutos', 'totalFornecedores', 'totalLojas', 'totalEstoque'));
})->name('dashboard');

// Rotas do CRUD de Produtos (Já cadastradas)
Route::resource('products', ProductController::class);

// Registra as 7 rotas padrões para o CRUD de Lojas
Route::resource('stores', StoreController::class);

// Registra as 7 rotas padrões para o CRUD de Fornecedores
Route::resource('suppliers', SupplierController::class);
