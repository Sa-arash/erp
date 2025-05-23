<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyUser extends Model
{
    use HasFactory;

    protected $fillable=['user_id','company_id'];
    
    public function getLogAttribute(){
        return $this?->user?->name;
    }
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
   
}
