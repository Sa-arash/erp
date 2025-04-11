<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use  HasFactory;
    protected $guarded=['id'];
    protected $casts=['exchange_rate'=>'double'];

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function accounts(){
        return $this->hasMany(Account::class, 'currency_id');
    }
    public function transactions(){
        return $this->hasMany(Transaction::class, 'currency_id');
    }
    public function parties(){
        return $this->hasMany(Parties::class, 'currency_id');
    }
    public function purchaseRequest(){
        return $this->hasMany(PurchaseRequest::class, 'currency_id');
    }
    public function banks(){
        return $this->hasMany(Bank::class, 'currency_id');
    }
    
}
