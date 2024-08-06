<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierProductController extends Controller
{
    public function index(): View
    {
        $products = auth()->user()
            ->products()
            ->paginate();

        return view('suppliers.products.index', ['products' => $products]);
    }

    public function generate(): RedirectResponse
    {
        Product::factory()
            ->state([
                'user_id' => auth()->id()
            ])
            ->count(rand(2,10))
            ->create();

        return redirect()->back();
    }
}
