<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class QuickBooksController extends Controller
{
    public function index(): View
    {
        return view('quickbooks.index');
    }

    public function handleCallback()
    {

    }
}
