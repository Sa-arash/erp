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
        'address',
    ];
    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
    public function structures(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Structure::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'inventories')
            ->withPivot(['quantity', 'structure_id', 'company_id'])
            ->withTimestamps();
    }
}
