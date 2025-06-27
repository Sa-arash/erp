<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Parties extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'type',
        'address',
        'phone',
        'email',
        'account_number',
        'account_vendor',
        'account_customer',
        'company_id',
        'currency_id',
        'account_code_vendor',
        'account_code_customer',
        'status'
    ];

    public function getLogAttribute(){

        if ($this->type==="vendor"){
            return $this?->type ."#-#".$this?->name ."#-#". $this?->accountVendor?->code ;
        }

        return $this?->type ."#-#".$this?->name ."#-#".$this?->accountCustomer?->code;
    }

    public function getInfoAttribute()
    {
        if ($this->type==="vendor"){
            return $this->name . "(" . $this->accountVendor?->code  . ")";
        }

        return $this->name . "(" .$this->accountCustomer?->code . ")";
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function currency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function accountVendor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_vendor');
    }

    public function accountCustomer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_customer');
    }

    public function purchaseOrderItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class,'vendor_id');
    }
}
