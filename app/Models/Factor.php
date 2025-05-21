<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Factor extends Model
{
    protected $guarded=['id'];

    
    public function getLogAttribute(){
        return $this?->title."#-#".$this?->type."#-#".$this?->invoice?->number."#-#".$this?->from."#-#".$this?->to;
    }


    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function invoice(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
    public function account(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Account::class,'account_id');
    }
    public function currency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
    public function party(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Parties::class);
    }
    public function items(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(FactorItem::class);
    }

}
