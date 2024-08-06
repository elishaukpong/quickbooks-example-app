<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BuyerProductController extends Controller
{
    public function index(): View
    {
        $products = Product::paginate();

        return view('buyers.products.index', ['products' => $products]);
    }
}
