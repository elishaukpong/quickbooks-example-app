<?php

namespace App\Contracts;

use Illuminate\Http\Request;

interface AccountingService
{
    public function connect(): string;

    public function handleCallback(array $options);

}