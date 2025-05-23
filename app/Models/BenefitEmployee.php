<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BenefitEmployee extends Model
{
    use HasFactory;
    protected $fillable=['company_id','benefit_id'];

    public function getLogAttribute(){
        return $this?->created_at;
    }
}
