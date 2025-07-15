<?php

namespace App\Models;

use App\Enums\POStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class PurchaseRequest extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'request_date',
        'purchase_number',
        'description',
        'status',
        'comment',
        'company_id',
        'employee_id',
        'is_quotation',
        'currency_id',
        'need_change'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('Purchase Request')
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {

        $property = $activity->properties->toArray();

        if (isset($property['old']) and isset($property['old']['employee_id']) and isset($property['old']['purchase_number']) )
            $activity->description = "Employee : " . Employee::query()->firstWhere('id', $property['old']['employee_id'])->fullName . " PR No " . $property['old']['purchase_number'];
        if (isset($property['attributes']) and isset($property['attributes']['employee_id']) and isset($property['attributes']['purchase_number']))
            $activity->description = "Employee : " . Employee::query()->firstWhere('id', $property['attributes']['employee_id'])->fullName . " PR No " . $property['attributes']['purchase_number'];
    }

    protected static function booted()
    {
        static::deleting(function ($purchaseRequest) {
            $purchaseRequest->items()->withTrashed()->get()->each(function ($item)use($purchaseRequest) {
                activity()->useLog('Delete PR Item')
                    ->performedOn($item)->setEvent('Delete')
                    ->withProperties(['PR No'=>$purchaseRequest->purchase_number,'Product' => $item->product->info, 'Description' => $item->description, 'Quantity' => $item->quantity, 'ETC' => $item->estimated_unit_cost])
                    ->log('  Delete Item ' . $item->product->info.' PR No: '.$purchaseRequest->purchase_number);
            });
        });
    }

    public function getLogAttribute()
    {
        return $this?->purchase_number . "#-#" . $this?->request_date;
    }

    protected $casts = ['status' => POStatus::class];

    public function approvals(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Approval::class, 'approvable', 'approvable_type', 'approvable_id');
    }

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function structure(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Structure::class);
    }

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
    public function purchaseOrder(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PurchaseOrder::class);
    }


    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class, 'purchase_request_id');
    }

    public function bid(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Bid::class);
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function currency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function quotations(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function quotationItems(): \Illuminate\Database\Eloquent\Relations\hasManyThrough
    {
        return $this->hasManyThrough(QuotationItem::class, Quotation::class);
    }
}
