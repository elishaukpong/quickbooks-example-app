<?php

namespace App\Services;

use App\Contracts\AccountingService;
use App\Models\QuickBooks;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Exception\SdkException;
use QuickBooksOnline\API\Exception\ServiceException;
use QuickBooksOnline\API\Facades\Customer;
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
//        $expenseData = [
//            "AccountRef" => [
//                "value" => "EXPENSE_ACCOUNT_ID",
//                "name" => "Expense Account"
//            ],
//            "PaymentType" => "CreditCard",
//            "EntityRef" => [
//                "value" => "VENDOR_ID",
//                "type" => "Vendor"
//            ],
//            "TotalAmt" => 100.00,
//            "Line" => [
//                [
//                    "DetailType" => "AccountBasedExpenseLineDetail",
//                    "Amount" => 100.00,
//                    "AccountBasedExpenseLineDetail" => [
//                        "AccountRef" => [
//                            "value" => "EXPENSE_ACCOUNT_ID",
//                            "name" => "Expense Account"
//                        ]
//                    ]
//                ]
//            ]
//        ];
    }

    public function addSales($accessToken, $refreshToken)
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

    public function getExpenses()
    {
        return $this->query("SELECT * FROM Purchase") ?? [];
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

        if (now()->gt($quickBooks->expires_in)) {
            $token = $oauth2LoginHelper->refreshToken();

            $quickBooks->update([
                'access_token' => $token->getAccessToken(),
                'refresh_token' => $token->getRefreshToken(),
                'expires_in' => $token->getAccessTokenExpiresAt(),
            ]);
        }

        $this->setAccessToken($quickBooks->fresh());
    }
}