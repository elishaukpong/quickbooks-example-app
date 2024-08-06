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
        $OAuth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
        return $OAuth2LoginHelper->getAuthorizationCodeURL();
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

    public function addExpenses(array $options)
    {
        $this->setAccessTokenWithRefreshAbilities();

        try{

            $purchaseData = [
                "AccountRef" => [
                    "name" => "Accounts Receivable",
                    "value" => "92",
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
                                "name" => "Uncategorized Expense",
                                "value" => "126",
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

    public function getExpenses()
    {
        return $this->query("SELECT * FROM Purchase") ?? [];
    }

    public function addSalesFor(User $user, array $options)
    {

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
    public function setAccessTokenWithRefreshAbilities(): void
    {
        $oauth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
        $quickBooks = auth()->user()->quickbooks;

        $this->setAccessToken($quickBooks->fresh());


        if (now()->gt($quickBooks->expires_in)) {
            $token = $oauth2LoginHelper->refreshToken();

            $quickBooks->update([
                'access_token' => $token->getAccessToken(),
                'refresh_token' => $token->getRefreshToken(),
                'expires_in' => $token->getAccessTokenExpiresAt(),
            ]);

            $this->setAccessToken($quickBooks->fresh());
        }

    }
}