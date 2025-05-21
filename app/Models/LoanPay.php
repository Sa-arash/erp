<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanPay extends Model
{
    use HasFactory;
    protected $guarded=['id'];

    public function getLogAttribute(){
        return $this?->payment_date."#-#".$this?->amount_pay;
    }
  
}
