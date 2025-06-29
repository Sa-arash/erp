<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    /** @use HasFactory<\Database\Factories\InventoryFactory> */
    use HasFactory;
    protected $fillable = [
        'warehouse_id',
        'product_id',
        'structure_id',
        'quantity',
        'company_id',
    ];

//    public function getLogAttribute(){
//        return $this->product?->title."#-#".$this?->warehouse?->title."#-#".$this?->structure?->title;
//    }


    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function warehouse(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function stocks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->HasMany(Stock::class);
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    public function structure(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Structure::class);
    }
}
