<?php

use App\Http\Controllers\BuyerController;
use App\Http\Controllers\QuickBooksController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('buyer', [BuyerController::class,'index']);
Route::get('supplier', [SupplierController::class,'index']);
Route::get('quickbooks/connect', [QuickBooksController::class,'index']);
Route::post('quickbooks/connect', [QuickBooksController::class,'index'])->name('quickbooks.auth');
Route::get('quickbooks/connect/callbacck', [QuickBooksController::class,'index']);
