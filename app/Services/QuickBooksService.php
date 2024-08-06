<?php

namespace App\Services;

use App\Contracts\AccountingService;
use Illuminate\Http\Request;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Exception\SdkException;
use QuickBooksOnline\API\Exception\ServiceException;

class QuickBooksService implements AccountingService
{
    protected DataService $dataService;
    protected OAuth2AccessToken $oAuth2AccessToken;

    public function __construct(
        protected string $clientID,
        protected string $clientSecret,
        protected string $redirectUri,
        protected string $environment
    )
    {
        $this->dataService = DataService::Configure([
            'auth_mode' => 'oauth2',
            'ClientID' => $this->clientID,
            'ClientSecret' => $this->clientSecret,
            'RedirectURI' => $this->redirectUri,
            'scope' => 'com.intuit.quickbooks.accounting',
            'baseUrl' => $this->environment
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

    public function addExpenses($accessToken, $refreshToken)
    {

    }

    public function getExpenses($accessToken, $refreshToken)
    {

    }

    public function addSales($accessToken, $refreshToken)
    {

    }

    public function getSales($accessToken, $refreshToken)
    {

    }

    public function setAccessToken($accessToken, $refreshToken, $realmId)
    {
        $this->oAuth2AccessToken->setAccessToken($accessToken);
        $this->oAuth2AccessToken->setRefreshToken($refreshToken);
        $this->oAuth2AccessToken->setRealmID($realmId);

        return $this;
    }

    public function refreshToken($refreshToken)
    {

    }

    public function query(string $string)
    {
        $dataService =  $this->dataService->updateOAuth2Token($this->oAuth2AccessToken);
        $oauth2LoginHelper = $dataService->getOAuth2LoginHelper();

        if($oauth2LoginHelper->isAccessTokenExpired())

        return $dataService->Query($string);
    }
}