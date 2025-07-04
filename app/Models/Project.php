<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Project extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded=['id'];
    public function getLogAttribute(){
        return $this?->name."#-#".$this?->code;
    }

    protected $casts=[
        'members'=>'array',
        'tags'=>'array',
        'files'=>'array',
    ];
    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
    public function tasks(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(Task::class);
    }
    public function purchaseRequestItem(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class,'project_id');
    }
}
