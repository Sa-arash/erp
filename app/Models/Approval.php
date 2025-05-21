<?php

namespace App\Models;
use App\Enums\ApprovalStatus;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Approval extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['status' => ApprovalStatus::class];

//     approvable
// approve_date
// comment
// status
// position
// employee_id
// company_id
    public $count=0;

    public function getLogAttribute() {
        return substr($this?->approvable_type, 11) . "#-#" . $this?->approve_date . "#-#" . $this?->status->value;
    }

    public function approvable(): MorphTo
    {

        return $this->morphTo();
    }

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
