<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $guarded = ['id'];

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    protected $appends=['total'];

    public function getLogAttribute(){
        return $this?->product?->title."#-#".$this?->purchaseOrder?->purchase_orders_number;
    }

    public function getTotalAttribute(){
        $freights = $this->freights ;
        $q = $this->quantity;
        $tax = $this->taxes;
        $price = $this->unit_price;

        return ($q * $price) + (($q * $price * $tax) / 100) + (($q * $price * $freights) / 100);

    }

    public function purchaseOrder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class , 'purchase_order_id');
    }
    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    public function unit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
