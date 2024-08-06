<?php

namespace App\Contracts;

use Illuminate\Http\Request;

interface AccountingService
{
    public function connect(): string;

    public function handleCallback(array $options);

    public function addExpenses($accessToken, $refreshToken);

    public function getExpenses($accessToken, $refreshToken);

    public function addSales($accessToken, $refreshToken);

    public function getSales($accessToken, $refreshToken);

    public function query(string $string);

    public function setAccessToken($accessToken, $refreshToken, $realmId);

}