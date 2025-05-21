<?php

namespace App\Models;

use App\Enums\ChequeStatus;
use Illuminate\Database\Eloquent\Model;

class Cheque extends Model
{
    protected $fillable=['type',"bank_name","branch_name","account_number","amount","issue_date","due_date","status","payer_name","payee_name","description","company_id","cheque_number",'transaction_id'];
    public function getLogAttribute(){
        return $this?->cheque_number."#-#".$this?->account_number."#-#".$this?->transaction?->invoice?->number."#-#".$this?->amount;
    }
    protected $casts=['status'=>ChequeStatus::class];
    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function transaction(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
