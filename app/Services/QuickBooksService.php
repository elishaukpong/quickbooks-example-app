<?php

namespace App\Services;

use App\Contracts\AccountingService;
use App\Models\QuickBooks;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Exception\SdkException;
use QuickBooksOnline\API\Exception\ServiceException;
use QuickBooksOnline\API\Facades\Account;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Purchase;
use QuickBooksOnline\API\Facades\SalesReceipt;
use QuickBooksOnline\API\Facades\Vendor;

class QuickBooksService implements AccountingService
{
    protected DataService $dataService;
    protected OAuth2AccessToken $oAuth2AccessToken;

    public function __construct(
        protected string $clientID,
        protected string $clientSecret,
        protected string $redirectUri,
        protected string $environment,
        protected string $baseUrl,
    )
    {
        $this->dataService = DataService::Configure([
            'auth_mode' => 'oauth2',
            'ClientID' => $this->clientID,
            'ClientSecret' => $this->clientSecret,
            'RedirectURI' => $this->redirectUri,
            'scope' => 'com.intuit.quickbooks.accounting',
            'baseUrl' => $this->baseUrl
        ]);

        $this->oAuth2AccessToken = new OAuth2AccessToken(
            $this->clientID,
            $this->clientSecret,
            $this->redirectUri
        );
    }

    public function connect(): string
    {
        return $this->dataService->getOAuth2LoginHelper()
            ->getAuthorizationCodeURL();
    }

    /**
     * @throws ServiceException
     * @throws SdkException
     */
    public function handleCallback(array $options)
    {
        $OAuth2LoginHelper = $this->dataService->getOAuth2LoginHelper();

        return $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($options['code'], $options['realmId']);
    }

    /**
     * @throws ServiceException
     * @throws SdkException
     */
    public function addExpenses(array $options)
    {
        $this->setAccessTokenWithRefreshAbilities();

        try{

            $purchaseData = [
                "AccountRef" => [
                    "name" => "Undeposited Funds",
                    "value" => "163",
                ],
                "EntityRef" => [
                    "value" => $options['vendor_id'],
                    "type" => "Vendor"
                ],
                "TotalAmt" => $options['price'],
                "Line" => [
                    [
                        "DetailType" => "AccountBasedExpenseLineDetail",
                        "Amount" => $options['price'],
                        "AccountBasedExpenseLineDetail" => [
                            "AccountRef" => [
                                "name" => "Gross Receipts",
                                "value" => "165",
                            ]
                        ]
                    ]
                ],
                "PaymentType" => "Cash",
            ];

            $purchase = Purchase::create($purchaseData);

            $result = $this->dataService->Add($purchase);

            if (!$result) {
                $error = $this->dataService->getLastError();
                Log::info("Error: " . $error->getResponseBody());
                return null;
            }

            return $result;
        }catch (\Exception $e) {
            Log::info($e->getMessage());
        }
    }

    /**
     * @throws ServiceException
     * @throws SdkException
     */
    public function getExpenses()
    {
        $this->setAccessTokenWithRefreshAbilities();

        return $this->query("SELECT * FROM Purchase") ?? [];
    }

    /**
     * @throws ServiceException
     * @throws SdkException
     */
    public function addSalesFor(User $user, array $options)
    {
        $this->setAccessTokenWithRefreshAbilities($user);

        try{

            $salesReceiptData = [
                "CustomerRef" => [
                    "value" => $options['customer_id'],
                    "name" => $options['customer_name']
                ],
                "PaymentType" => "Cash",
                "Line" => [
                    [
                        "DetailType" => "SalesItemLineDetail",
                        "Amount" => $options['price'],
                        "SalesItemLineDetail" => [
                            "UnitPrice" => $options['price'],
                            "Qty" => 1
                        ]
                    ]
                ],
                "BillEmail" => [
                    "Address" => $options['customer_email']
                ]
            ];

            $salesReceipt = SalesReceipt::create($salesReceiptData);

            $result = $this->dataService->Add($salesReceipt);

            if (!$result) {
                $error = $this->dataService->getLastError();
                Log::info("Error herrrrr: " . $error->getResponseBody());
                return null;
            }

            return $result;
        }catch (\Exception $e) {
            Log::info('hi'.$e->getMessage());
        }

    }

    public function getSales()
    {
        return $this->query("SELECT * FROM SalesReceipt") ?? [];
    }

    public function setAccessToken(QuickBooks $quickBooks)
    {
        Log::info('Setting Access Token', ['access_token' => $quickBooks->access_token]);

        $this->oAuth2AccessToken->setAccessToken($quickBooks->access_token);
        $this->oAuth2AccessToken->setRefreshToken($quickBooks->refresh_token);
        $this->oAuth2AccessToken->setRealmID($quickBooks->realm_id);

        $this->dataService->updateOAuth2Token($this->oAuth2AccessToken);

        return $this;
    }

    /**
     * @throws ServiceException
     * @throws SdkException
     */
    public function query(string $string)
    {
        $this->setAccessTokenWithRefreshAbilities();

        return $this->dataService->Query($string);
    }

    public function createCustomer(array $options)
    {
        $this->setAccessTokenWithRefreshAbilities();

        $customer = Customer::create([
            "DisplayName" => $options['name'],
            "PrimaryEmailAddr" => [
                "Address" => $options['email']
            ]
        ]);

        return $this->dataService->Add($customer);
    }

    public function createVendor(array $options)
    {
        $this->setAccessTokenWithRefreshAbilities();

        $vendor = Vendor::create([
            "DisplayName" => $options['name'],
            "PrimaryEmailAddr" => [
                "Address" => $options['email']
            ]
        ]);

        return $this->dataService->Add($vendor);
    }

    /**
     * @return void
     * @throws SdkException
     * @throws ServiceException
     */
    public function setAccessTokenWithRefreshAbilities(?User $user = null): void
    {
        $user = $user ?? auth()->user();
        $quickBooks = $user->quickbooks;

        Log::info('Setting Access Token With Refresh Abilities', ['quickBooks' => $quickBooks]);


        $this->setAccessToken($quickBooks);

        if (now()->gt($quickBooks->expires_in)) {
            $oauth2LoginHelper = $this->dataService->getOAuth2LoginHelper();

            try {
                $newAccessToken = $oauth2LoginHelper->refreshAccessTokenWithRefreshToken($quickBooks->refresh_token);

                // Update the QuickBooks model with new tokens and expiration time
                $quickBooks->update([
                    'access_token' => $newAccessToken->getAccessToken(),
                    'refresh_token' => $newAccessToken->getRefreshToken(),
                    'expires_in' => $newAccessToken->getAccessTokenExpiresAt(),
                ]);

                // Re-set the access token with the new tokens
                $this->setAccessToken($quickBooks->fresh());
            } catch (\Exception $e) {
                Log::error('Error refreshing QuickBooks token: ' . $e->getMessage());
                throw $e;
            }
        }

    }
}