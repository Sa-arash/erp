<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Benefit extends Model
{
    use HasFactory;
    protected $fillable = ['built_in','title', 'amount','type','on_change','company_id','price_type','percent'];



    public function getLogAttribute(){
        return $this?->title."#-#".$this?->type."#-#".$this?->price_type."#-#".$this?->amount;
    }
    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
