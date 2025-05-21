<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['position_procurement_controller' => 'array', 'procurement_committee_members' => 'array'];

    public function getLogAttribute(){
        return $this?->purchaseRequest?->purchase_number."#-#".$this?->opening_date;
    }
//   
    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function quotation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function party(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Parties::class);
    }

    public function purchaseRequest(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }
}
