<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskEmployee extends Model
{
    public function getLogAttribute(){
        return $this?->employee?->fullName."#-#".$this?->task?->title;
    }
    public function employee(){
        return $this->belongsTo(Employee::class);
    }
    public function task(){
        return $this->belongsTo(Task::class);
    }
}
