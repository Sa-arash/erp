<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Person extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = ['id'];
    protected $table='persons';

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function assets(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(Asset::class,'check_out_person');
    }
    public function assetEmployee(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(AssetEmployee::class,'person_id');
    }

}
