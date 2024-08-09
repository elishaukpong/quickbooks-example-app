<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuickBooks extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'expires_in' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(QuickBooksVendor::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(QuickBooksCustomer::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(QuickBooksAccount::class);
    }
}
