<?php

namespace App\Models;

use App\Enums\LeaveStatus2;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UrgentLeave extends Model
{
    use HasFactory;

    protected $guarded=['id'];
    protected $casts = ['status' => LeaveStatus2::class];

    public function getLogAttribute(){
        return $this?->employee?->fullName."#-#".$this?->hours."#-#".$this?->date;
    }

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
        return $this->belongsTo(Employee::class, 'admin_id');
    }

}
