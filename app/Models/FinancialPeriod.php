<?php

namespace App\Models;

use App\Enums\PeriodStatus;
use Illuminate\Database\Eloquent\Model;

class FinancialPeriod extends Model
{
    protected $fillable=['name','start_date','end_date','company_id','status'];

    public function getLogAttribute(){
        return $this?->name."#-#".$this?->start_date."#-#".$this?->end_date."#-#".$this?->status->value;
    }

    protected $casts=['status'=>PeriodStatus::class];
    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Transaction::class,'financial_period_id');
    }

    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Invoice::class,Transaction::class,'financial_period_id','id','id','invoice_id')->distinct();
    }

}
