<?php

namespace App\Http\Controllers;

use App\Models\QuickBooksAccount;

class MakeAccountDefaultController extends Controller
{
    public function __invoke($account)
    {
        QuickBooksAccount::findOrFail($account)->update([
            'is_default' => true
        ]);

        return redirect()->back();

    }
}
