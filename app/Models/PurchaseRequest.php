<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_date',
        'employee_id',
        'purchase_number',
        'description',
        'status',
        'warehouse_comment',
        'department_manager_comment',
        'ceo_comment',
        'general_comment',
        'warehouse_status_date',
        'department_manager_status_date',
        'ceo_status_date',
        'purchase_date',
        'company_id',
        'department_id',
        'structure_id',
    ];


    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function structure(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Structure::class);
    }

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }


    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class, 'purchase_request_id');
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function quotations(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(Quotation::class);
    }
}
