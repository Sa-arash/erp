<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Task extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = ['id'];
    protected $casts=['status'=>TaskStatus::class];

    public function getLogAttribute(){
        return $this?->employee?->fullName."#-#".$this?->title."#-#".$this?->status;
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
    public function employees(): \Illuminate\Database\Eloquent\Relations\belongsToMany
    {
        return $this->belongsToMany(Employee::class,'task_employees');
    }
    public function reports(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(TaskReports::class);
    }
}
