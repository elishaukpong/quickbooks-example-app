<?php

use App\Http\Controllers\BuyerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuickBooksController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('buyer', [BuyerController::class,'index']);
    Route::get('supplier', [SupplierController::class,'index']);
    Route::get('quickbooks', [QuickBooksController::class,'index'])->name('quickbooks.index');
    Route::get('quickbooks/connect/auth', [QuickBooksController::class,'connect'])->name('quickbooks.auth');
    Route::get('quickbooks/callback', [QuickBooksController::class,'handleCallback']);

});

require __DIR__.'/auth.php';
