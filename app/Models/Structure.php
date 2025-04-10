<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Structure extends Model
{
    use HasFactory;
    protected $fillable=[
        'title',
        'parent_id',
        'warehouse_id',
        'type',
        'company_id',
        'sort',
        'location'
    ];
    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function employees(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(Employee::class);
    }
    public function assets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Asset::class);
    }
    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(__CLASS__,'parent_id');
    }
    public function chiller(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(__CLASS__,'parent_id','id');
    }
    public function warehouse(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
