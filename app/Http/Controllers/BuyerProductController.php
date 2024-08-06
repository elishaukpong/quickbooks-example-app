<?php

namespace App\Http\Controllers;

use App\Contracts\AccountingService;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class BuyerProductController extends Controller
{
    public function __construct(protected AccountingService $accountingService)
    {
        //
    }

    public function index(): View
    {
        $products = Product::paginate();

        return view('buyers.products.index', ['products' => $products]);
    }

    public function purchase($productId)
    {
        try {
            $product = Product::with('user.quickbooks')->findOrFail($productId);

            $this->recordPurchaseFor($product);
            $this->recordSalesFor($product);

            return redirect()->route('buyer.index');

        }catch(\Exception $e){
            Log::info($e->getMessage());
        }
    }

    private function recordPurchaseFor($product): void
    {
        $this->accountingService->addExpenses([
            'price' => $product->price,
            'vendor_id' => $product->user->quickbooks->vendor_id
        ]);
    }

    private function recordSalesFor($product): void
    {
        $this->accountingService->addSales([
            'price' => $product->price,
            'vendor_id' => $product->user->quickbooks->vendor_id
        ]);
    }
}
