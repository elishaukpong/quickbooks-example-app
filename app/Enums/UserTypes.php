<?php

namespace App\Enums;

enum UserTypes: string
{
    case BUYER = 'buyer';
    case SUPPLIER = 'supplier';

    public function label(): string
    {
        return __(ucfirst($this->value));
    }
}
