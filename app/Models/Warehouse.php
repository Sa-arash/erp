<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Warehouse extends Model
{
    /** @use HasFactory<\Database\Factories\WarehouseFactory> */
    use HasFactory;
    protected $fillable = [
        'title',
        'employee_id',
        'company_id',
        'phone',
        'country',
        'state',
        'city',
        'type',
        'address',
    ];
    public function getLogAttribute(){
        return $this?->title;
    }
    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
    public function employees(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(Employee::class);
    }
    public function inventories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Inventory::class);
    }
    public function stocks(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Stock::class,Inventory::class);
    }
    public function assets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Asset::class);
    }
    public function structures(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Structure::class,'warehouse_id','id');
    }
}
