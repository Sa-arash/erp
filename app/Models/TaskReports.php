<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskReports extends Model
{
    protected $guarded=['id'];
    public function getLogAttribute(){
        return $this?->employee?->fullName."#-#".$this?->task?->title."#-#".$this?->date;
    }
    public function employee(){
        return $this->belongsTo(Employee::class);
    }

}
