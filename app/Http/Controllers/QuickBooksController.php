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

            $user = auth()->user();

            $user->quickbooks()
                ->create([
                    'access_token' => $accessTokenDetails->getAccessToken(),
                    'refresh_token' => $accessTokenDetails->getRefreshToken(),
                    'realm_id' => $accessTokenDetails->getRealmId(),
                    'expires_in' => now()->addSeconds($accessTokenDetails->getAccessTokenValidationPeriodInSeconds())
                ]);

            if($user->isBuyer()) {
                $customerDetails = $this->accountingService->createCustomer([
                    'name' => $user->name,
                    'email' => $user->email,
                ]);

                $extraDetails['customer_id'] = $customerDetails->Id;

            } else {
                $vendorDetails = $this->accountingService->createVendor([
                    'name' => $user->name,
                    'email' => $user->email,
                ]);

                $extraDetails['vendor_id'] = $vendorDetails->Id;
            }

            $user->quickbooks()->update($extraDetails);

            return redirect()->route('quickbooks.index')->with('success', 'Connected Quickbooks Successfully!');
        }catch (ServiceException|SdkException $e) {
            Log::info($e->getMessage());
            return redirect()->route('quickbooks.index')->with('error', $e->getMessage());
        }
    }

    public function list()
    {
        $user = auth()->user();

        if(auth()->user()->isBuyer()) {
            $customerDetails = $this->accountingService->query("SELECT * FROM vendor");

            dd($customerDetails);
        } else {
            $this->accountingService->createVendorFor(auth()->user());
        }
    }
}
