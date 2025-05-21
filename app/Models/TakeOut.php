<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TakeOut extends Model
{
    protected $guarded=['id'];
    protected $casts=['itemsOut'=>'array'];

    public function getLogAttribute(){
        return $this?->employee?->fullName."#-#".$this?->date;
    }
    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TakeOutItem::class);
    }
    public function assets(){
        return $this->hasManyThrough(Asset::class,TakeOutItem::class,'take_out_id','id','id','asset_id');
    }

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvals(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Approval::class,'approvable','approvable_type','approvable_id');
    }

}
