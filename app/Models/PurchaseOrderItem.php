<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PurchaseOrderItem extends Model
{
    use LogsActivity;
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('Purchase Order Item')
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
    protected $guarded = ['id'];

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getLogAttribute(){
        return $this?->product?->title."#-#".$this?->purchaseOrder?->purchase_orders_number;
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
    public function vendor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Parties::class);
    }
    public function currency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
