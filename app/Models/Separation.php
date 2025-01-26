<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Separation extends Model
{
    protected $guarded=['id'];

    protected $casts=['comments_signature'=>'array'];

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }
}
