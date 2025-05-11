<?php

namespace App\Models;

use App\Enums\LeaveStatus;
use App\Enums\LeaveStatus2;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UrgentLeave extends Model
{
    use HasFactory;

    protected $guarded=['id'];
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

    public function typeLeave(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Typeleave::class, 'urgent_typeleave_id', 'id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
