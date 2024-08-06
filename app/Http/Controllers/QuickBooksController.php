<?php

namespace App\Http\Controllers;

use App\Contracts\AccountingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuickBooksController extends Controller
{
    public function __construct(protected AccountingService $accountingService)
    {
    }

    public function index(): View
    {
        return view('quickbooks.index');
    }

    public function connect(): RedirectResponse
    {
        return redirect($this->accountingService->connect());
    }

    public function handleCallback()
    {

    }
}
