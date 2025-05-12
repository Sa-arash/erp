<?php

namespace App\Models;

use App\Enums\LeaveStatus;
use App\Enums\LeaveStatus2;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = ['type','explain_leave','is_circumstances','admin_id','comment','employee_id', 'typeleave_id', 'start_leave', 'end_leave', 'days', 'document', 'description', 'status','user_id','approval_date', 'company_id'];

    protected $casts = ['status' => LeaveStatus2::class];

    public function approvals(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Approval::class, 'approvable', 'approvable_type', 'approvable_id');
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
    public function admin(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class,'admin_id');
    }

    public function typeLeave(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Typeleave::class, 'typeleave_id', 'id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
