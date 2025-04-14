<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionHistoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/transactions', [TransactionHistoryController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/create', [TransactionHistoryController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionHistoryController::class, 'store'])->name('transactions.store');
});

Route::resource('products', ProductController::class);
Route::patch('/products/{product}/add-stock', [ProductController::class, 'addStock'])->name('products.add-stock');

Route::middleware(['auth', 'role:Administrator'])->group(function () {
    Route::resource('users', UserController::class);
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

require __DIR__.'/auth.php';
