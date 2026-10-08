<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

Route::get('/', function() { return redirect()->route('chat.index'); });

Route::get('/chat/{id?}', [ChatController::class, 'index'])->name('chat.index');
Route::post('/chat/new', [ChatController::class, 'newSession'])->name('chat.new');
Route::post('/chat/send/{id}', [ChatController::class, 'sendMessage'])->name('chat.send');
Route::post('/chat/clear/{id}', [ChatController::class, 'clearChat'])->name('chat.clear');