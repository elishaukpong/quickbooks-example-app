<?php

namespace App\Services;

use App\Contracts\AccountingService;
use App\Models\QuickBooks;
use App\Models\QuickBooksCustomer;
use App\Models\QuickBooksVendor;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Exception\IdsException;
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
            $vendor = $this->getVendorDetailsFor($options['vendor']);

            $purchaseData = [
                "AccountRef" => [
                    "name" => "Billable Expense Income",
                    "value" => "125",
                ],
                "EntityRef" => [
                    "value" => $vendor->vendor_id,
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

            $customer = $this->getCustomerDetailsFor($user, $options['customer']);

            $salesReceiptData = [
                "CustomerRef" => [
                    "value" => $customer->customer_id,
                    "name" => $customer->user->name,
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
                    "Address" => $customer->user->email
                ]
            ];

            $salesReceipt = SalesReceipt::create($salesReceiptData);

            $result = $this->dataService->Add($salesReceipt);

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

    public function getSales()
    {
        return $this->query("SELECT * FROM SalesReceipt") ?? [];
    }

    public function setAccessToken(QuickBooks $quickBooks)
    {
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
//        $this->setAccessTokenWithRefreshAbilities();

        return $this->dataService->Query($string);
    }

    /**
     * @throws ServiceException
     * @throws SdkException
     * @throws IdsException
     */
    public function createCustomer(array $options, ?User $user = null)
    {
        $this->setAccessTokenWithRefreshAbilities($user);

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
    protected function setAccessTokenWithRefreshAbilities(?User $user = null): void
    {
        $user = $user ?? auth()->user();
        $quickBooks = $user->quickbooks;

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

    private function getVendorDetailsFor(User $vendor): QuickBooksVendor
    {
        try {
            return auth()->user()
                ->quickbooks
                ->vendors()
                ->where('user_id', $vendor->id)
                ->firstOrFail();
        }catch(ModelNotFoundException $e) {
            $vendorDetails = $this->createVendor([
               'name' => $vendor->name,
               'email' => $vendor->email
            ]);

            return auth()->user()
                ->quickbooks
                ->vendors()
                ->create([
                   'user_id' => $vendor->id,
                   'vendor_id' => $vendorDetails->Id
                ]);
        }
    }

    private function getCustomerDetailsFor(User $user, User $customer): QuickBooksCustomer
    {
        try {
            return $user->quickbooks
                ->customers()
                ->where('user_id', $customer->id)
                ->firstOrFail();
        }catch(ModelNotFoundException $e) {
            $customerDetails = $this->createCustomer([
                'name' => $customer->name,
                'email' => $customer->email
            ], $user);

            return $user->quickbooks
                ->customers()
                ->create([
                    'user_id' => $customer->id,
                    'customer_id' => $customerDetails->Id
                ]);
        }
    }

    public function getAccountsFor(User $user): array
    {
        $this->setAccessTokenWithRefreshAbilities($user);

        return $this->query('SELECT * FROM Account') ?? [];
    }
}