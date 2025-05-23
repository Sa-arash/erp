<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documentation extends Model
{
    
    use HasFactory;


    protected $fillable=['title','file','company_id'];
    public function getLogAttribute(){
        return $this?->title."#-#".$this?->file;
    }
}
