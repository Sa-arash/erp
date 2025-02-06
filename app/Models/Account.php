<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    /** @use HasFactory<\Database\Factories\AccountFactory> */
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'name',
        'type',
        'stamp',
        'group',
        'code',
        'level',
        'parent_id',
        'built_in',
        'description',
        'company_id',
        'closed_at',
        'currency_id',
    ];
    public function getTitleAttribute(){
        return $this->name." (".$this->code." )";
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function currency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
    public function account(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(__CLASS__,'parent_id','id');
    }
    public function childerns(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(__CLASS__,'parent_id','id');
    }
    public function parent(): \Illuminate\Database\Eloquent\Relations\belongsTo
    {
        return $this->belongsTo(__CLASS__,'id','parent_id');
    }

    public function transactions(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(Transaction::class, 'account_id');
    }

    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Invoice::class, Transaction::class, 'account_id', 'id', 'id', 'invoice_id')->distinct();
    }

    public function bank(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Bank::class);
    }
//    public function accountType(): \Illuminate\Database\Eloquent\Relations\belongsTo
//    {
//        return $this->belongsTo(AccountType::class);
//    }

}
