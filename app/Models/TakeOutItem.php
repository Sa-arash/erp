<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TakeOutItem extends Model
{
    protected $guarded=['id'];

    public function getLogAttribute(){
        return $this?->employee?->fullName."#-#".$this?->asset?->product?->title."#-#".$this?->returned_date;
    }
    public function asset(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
    public function takeOut(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TakeOut::class);
    }
    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
