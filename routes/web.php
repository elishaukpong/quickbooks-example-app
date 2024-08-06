<?php

use App\Http\Controllers\BuyerController;
use App\Http\Controllers\BuyerProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuickBooksController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierProductController;
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

    Route::get('buyer', [BuyerController::class,'index'])->name('buyer.index');
    Route::get('supplier', [SupplierController::class,'index'])->name('supplier.index');

    Route::get('buyer/products', [BuyerProductController::class,'index'])->name('buyer.product.index');
    Route::get('buyer/products/{product}/purchase', [BuyerProductController::class,'purchase'])->name('buyer.product.purchase');

    Route::get('supplier/products', [SupplierProductController::class,'index'])->name('supplier.product.index');
    Route::get('supplier/products/generate', [SupplierProductController::class,'generate'])->name('supplier.product.generate');

    Route::get('quickbooks', [QuickBooksController::class,'index'])->name('quickbooks.index');
    Route::get('quickbooks/connect/auth', [QuickBooksController::class,'connect'])->name('quickbooks.auth');
    Route::get('quickbooks/callback', [QuickBooksController::class,'handleCallback']);
    Route::get('quickbooks/list', [QuickBooksController::class,'list']);

});

require __DIR__.'/auth.php';
