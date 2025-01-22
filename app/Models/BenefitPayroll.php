<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BenefitPayroll extends Model
{
    protected $fillable = ['company_id', 'amount', 'percent', 'benefit_id', 'payroll_id'];

    public function benefit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Benefit::class);
    }
}
