<?php

namespace App\Http\Controllers;

use App\Contracts\AccountingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use QuickBooksOnline\API\Exception\SdkException;
use QuickBooksOnline\API\Exception\ServiceException;

class QuickBooksController extends Controller
{
    public function __construct(protected AccountingService $accountingService)
    {
        //
    }

    public function index(): View
    {
        return view('quickbooks.index');
    }

    public function connect(): RedirectResponse
    {
        return redirect($this->accountingService->connect());
    }

    public function handleCallback(Request $request)
    {
        try {
            $accessTokenDetails = $this->accountingService->handleCallback($request->only('code', 'realmId'));

            auth()->user()
                ->quickbooks()
                ->create([
                    'access_token' => $accessTokenDetails->getAccessToken(),
                    'refresh_token' => $accessTokenDetails->getRefreshToken(),
                    'realm_id' => $accessTokenDetails->getRealmId(),
                    'expires_in' => now()->addSeconds($accessTokenDetails->getAccessTokenValidationPeriodInSeconds())
                ]);

            return redirect()->route('quickbooks.index')->with('success', 'Connected Quickbooks Successfully!');
        }catch (ServiceException|SdkException $e) {
            Log::info($e->getMessage());
            return redirect()->route('quickbooks.index')->with('error', $e->getMessage());
        }
    }

    public function list()
    {
        $quickBooks = auth()->user()->quickbooks;

        try {
            $expenseAccounts = $this->accountingService
                ->setAccessToken($quickBooks)
                ->query("SELECT * FROM Account WHERE AccountType = 'Expense'");
        }catch (\Exception $e) {
            dd($e->getMessage());
        }


//
        dd($expenseAccounts);
//
//        foreach ($expenseAccounts as $account) {
//            echo "Account ID: " . $account . " Name: " . $account->Name . "<br>\n";
//        }

    }
}
