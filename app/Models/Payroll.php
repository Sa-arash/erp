<?php

namespace App\Models;

use App\Enums\PayrollStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = ['invoice_id', 'total_allowance', 'total_deduction', 'account_id', 'employee_id', 'amount_pay', 'payment_date', 'start_date', 'end_date', 'status', 'user_id', 'company_id'];

    public function getLogAttribute()
    {
        return $this?->employee->fullName . "#-#" . $this?->amount_pay . "#-#" . $this?->start_date . "#-#" . $this?->end_date . "#-#" . $this?->status->value;
    }

    protected $casts = ['status' => PayrollStatus::class];
    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function account(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
    public function invoice(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
    public function benefits(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Benefit::class, 'benefit_payrolls')->withPivot(['amount', 'percent']);
    }
    public function items(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(BenefitPayroll::class);
    }
    public function itemAllowances(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(BenefitPayroll::class)->whereHas('benefit', fn($q) => $q->where('type', 'allowance'));
    }
    public function itemDeductions(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(BenefitPayroll::class)->whereHas('benefit', fn($q) => $q->where('type', 'deduction'));
    }
}
