<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PaymentController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/dashboard');
})->name('home');

Route::get('/login', function () {
    return redirect('/dashboard/login');
});

Route::get('/payments/getqrcode', [PaymentController::class, 'store'])->name('payments.qrcode')->middleware('auth');

Route::get('/payments/code', [PaymentController::class, 'page'])->name('payments.page')->middleware('auth');

Route::post('/payments/abacate', [PaymentController::class, 'pixwebhook'])->name('payments.webhook')->withoutMiddleware(VerifyCsrfToken::class);

Route::middleware('auth')
    ->prefix('/chats')
    ->group(function () {
        Route::get('/', [ChatController::class, 'index'])->name('chats.index');
        // Route::post('/', [ChatController::class, 'store'])->name('chats.store');

        Route::get('/create', [ChatController::class, 'create'])->name('chats.create');

        Route::get('/{chat}', [ChatController::class, 'show'])->name('chats.show');
        Route::post('/{chat}', [ChatController::class, 'update'])->name('chats.update');
        Route::delete('/{chat}/image', [ChatController::class, 'deleteImage'])->name('chats.delete-image');
        Route::post('/{chat}/messages', [MessageController::class, 'store'])->name('messages.store');

        Route::get('/{chat}/search-users', [ChatController::class, 'searchUsersToAdd'])
            ->name('chats.search-users');
        Route::post('/{chat}/users', [ChatController::class, 'addUsers'])->name('chats.add-users');
    });


require __DIR__ . '/settings.php';
