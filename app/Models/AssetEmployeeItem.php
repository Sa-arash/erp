<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetEmployeeItem extends Model
{

    protected $fillable = [
        'asset_employee_id',
        'asset_id',
        'due_date',
        'warehouse_id',
        'return_date',
        'type',
        'return_approval_date',
        'structure_id',
        'company_id',
    ];

    public function getLogAttribute(){
        return $this->asset?->product?->title."#-#".$this?->assetEmployee?->employee?->fullName."#-#".$this?->warehouse?->title."#-#".$this?->structure?->title;
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function warehouse(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function structure(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Structure::class);
    }

    public function assetEmployee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AssetEmployee::class);
    }
    public function asset(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    protected $guarded = ["id"];

}
