<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BenefitPayroll extends Model
{
    protected $fillable = ['company_id', 'amount', 'percent', 'benefit_id', 'payroll_id'];

    public function getLogAttribute(){
        return $this?->benefit?->title."#-#".$this?->amount;
    }

    public function benefit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Benefit::class);
    }
    public function payroll(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }
}
