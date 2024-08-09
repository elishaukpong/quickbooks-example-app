<?php

namespace App\Contracts;

use App\Models\QuickBooks;
use App\Models\User;

interface AccountingService
{
    public function connect(): string;

    public function handleCallback(array $options);

    public function addExpenses(array $options);

    public function getExpenses();

    public function addSalesFor(User $user, array $options);

    public function getSales();

    public function query(string $string);

    public function setAccessToken(QuickBooks $quickBooks);

    public function createCustomer(array $options);

    public function createVendor(array $options);

    public function getAccountsFor(User $user);

}