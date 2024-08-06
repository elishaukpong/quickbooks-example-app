<?php

namespace App\Services;

use App\Contracts\AccountingService;
use QuickBooksOnline\API\DataService\DataService;

class QuickBooksService implements AccountingService
{
    protected string $authMode;
    protected string $scope;

    public function __construct(
        protected string $clientID,
        protected string $clientSecret,
        protected string $redirectUri,
        protected string $environment
    )
    {
        $this->authMode = 'oauth2';
        $this->scope = 'com.intuit.quickbooks.accounting';
    }

    public function connect(): string
    {
        $dataService = DataService::Configure([
            'auth_mode' => $this->authMode,
            'ClientID' => $this->clientID,
            'ClientSecret' => $this->clientSecret,
            'RedirectURI' => $this->redirectUri,
            'scope' => $this->scope,
            'baseUrl' => $this->environment
        ]);

        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
        return $OAuth2LoginHelper->getAuthorizationCodeURL();
    }
}