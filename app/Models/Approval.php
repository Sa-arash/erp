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

    public $count=0;

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
