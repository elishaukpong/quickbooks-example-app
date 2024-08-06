<?php

namespace App\Http\Controllers;

use App\Contracts\AccountingService;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function __construct(protected AccountingService $accountingService)
    {
        //
    }

    public function index(): View
    {
        $quickBooks = auth()->user()->quickbooks;

        try {
            $sales = $this->accountingService
                ->setAccessToken($quickBooks)
                ->getSales();
        }catch (\Exception $e) {
            dd($e->getMessage());
        }

        return view('suppliers.index',['sales' => $sales]);
    }
}
