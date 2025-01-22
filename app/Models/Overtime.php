<?php

namespace App\Models;

use App\Enums\LeaveStatus;
use App\Enums\LeaveStatus2;
use Illuminate\Database\Eloquent\Model;

class Overtime extends Model
{
    protected $fillable = ['title', 'employee_id', 'company_id', 'user_id', 'approval_date', 'overtime_date','hours', 'status','comment'];

    protected $casts = ['status' => LeaveStatus2::class];

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }


}
