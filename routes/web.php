<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/dashboard');
})->name('home');

Route::middleware('auth')
    ->prefix('/chats')
    ->group(function () {
        Route::get('/', [ChatController::class, 'index'])->name('chats.index');
        Route::post('/', [ChatController::class, 'store'])->name('chats.store');
        Route::get('/{chat}', [ChatController::class, 'show'])->name('chats.show');
        Route::patch('/{chat}', [ChatController::class, 'update'])->name('chats.update');
        Route::delete('/{chat}/image', [ChatController::class, 'deleteImage'])->name('chats.delete-image');
        Route::post('/{chat}/messages', [MessageController::class, 'store'])->name('messages.store');
    });
// Route::middleware(['auth', 'verified'])->group(function () {
//     Route::get('dashboard', function () {
//         return Inertia::render('dashboard');
//     })->name('dashboard');
// });

require __DIR__ . '/settings.php';
