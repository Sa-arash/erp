<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Typeleave extends Model
{
    use HasFactory;

    protected $fillable=['sort','title','abbreviation','days','description','company_id','is_payroll','built_in'];

    public function getLogAttribute(){
        return $this?->title."#-#".$this?->abbreviation."#-#".$this?->days;
    }
    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
