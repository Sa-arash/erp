<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{

    protected $fillable = [
        'account_id',
        'creditor',
        'debtor',
        'reference',
        'description',
        'company_id',
        'user_id',
        'invoice_id',
        'financial_period_id',
        'creditor_foreign',
        'debtor_foreign',
        'currency_id',
        'exchange_rate',

    ];

    public function getLogAttribute(){
        return $this?->invoice?->number."#-#".$this?->account?->code."#-#".$this?->user?->employee?->fullName;
    }

    protected $casts = [
        'attribute' => 'array'
    ];

    public function financialPeriod(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FinancialPeriod::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }

    public function invoice(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function cheque(): \Illuminate\Database\Eloquent\Relations\hasOne
    {
        return $this->hasOne(Cheque::class);
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function currency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }



}
