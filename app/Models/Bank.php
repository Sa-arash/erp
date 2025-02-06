<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = [
        'name',
        'bank_name',
        'branch_name',
        'account_number',
        'account_holder',
        'account_type',
        'currency',
        'iban',
        'swift_code',
        'opening_balance',
        'current_balance',
        'description',
        'company_id',
        'account_id',
        'account_code',
        'type',
        'currency_id'
    ];

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function account(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
    public function currency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
