<?php

namespace App\Services;

use App\Contracts\AccountingService;

class QuickBooksService implements AccountingService
{
    public function __construct(
        protected string $key,
        protected string $secret,
        protected string $redirect,
        protected string $environment
    )
    {}

    public function connect()
    {

    }
}