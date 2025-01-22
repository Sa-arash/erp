<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyUser extends Model
{
    use HasFactory;

    protected $fillable=['user_id','company_id'];
    
    public function user(){
        $this->belongsTo(User::class);
    }
}
