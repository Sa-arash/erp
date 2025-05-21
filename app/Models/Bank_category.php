<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank_category extends Model
{
    /** @use HasFactory<\Database\Factories\BankCategoryFactory> */
    use HasFactory;

    protected $fillable = ['title', 'description', 'company_id', 'type', 'parent_id'];

    public function getLogAttribute(){
        return $this?->title."#-#".$this?->type."#-#".$this?->parent?->title;
    }
    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(__CLASS__, 'parent_id', 'id');
    }
    public function expenses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Expense::class, 'category_id', 'id');
    }

    public function incomes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Income::class, 'category_id', 'id');
    }
    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Bank_category::class, 'parent_id', 'id');
    }

}
