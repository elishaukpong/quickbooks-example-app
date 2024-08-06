<?php

namespace App\Contracts;

use App\Models\QuickBooks;

interface AccountingService
{
    public function connect(): string;

    public function handleCallback(array $options);

    public function addExpenses($accessToken, $refreshToken);

    public function getExpenses();

    public function addSales($accessToken, $refreshToken);

    public function getSales();

    public function query(string $string);

    public function setAccessToken(QuickBooks $quickBooks);

}