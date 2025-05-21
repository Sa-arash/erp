<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $guarded=['id'];
    public function getLogAttribute(){
        return $this?->title."#-#".$this?->quantity;
    }
    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function stocks(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(Stock::class);
    }
}
