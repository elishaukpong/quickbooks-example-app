<?php

namespace App\Http\Controllers;

use App\Contracts\AccountingService;
use Illuminate\View\View;

class BuyerController extends Controller
{
    public function __construct(protected AccountingService $accountingService)
    {
        //
    }

    public function index(): View
    {
        try {
            $expenses = $this->accountingService->getExpenses();
        }catch (\Exception $e) {
            dd($e->getMessage());
        }

        return view('buyers.index', ['expenses' => $expenses]);
    }
}
