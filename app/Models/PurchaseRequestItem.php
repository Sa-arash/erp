<?php

namespace App\Models;

use App\Enums\ItemStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PurchaseRequestItem extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use LogsActivity;
    use SoftDeletes;
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('Purchase Request Item')
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }


    protected $fillable = [
        'product_id',
        'description',
        'quantity',
        'estimated_unit_cost',
        'status',
        'unit_id',
        'purchase_request_id',
        'project_id',
        'company_id',
        'clarification_comment',
        'verification_comment',
        'approval_comment',
        'clarification_decision',
        'verification_decision',
        'approval_decision',
    ];
    public function tapActivity(Activity $activity, string $eventName): void
    {
        // تبدیل collection به آرایه
        $properties = $activity->properties->toArray();

        // تغییر مقدار status اگر هست
        if (isset($properties['attributes']['status'])) {
            $properties['attributes']['status'] = $this->statusToLabel($properties['attributes']['status']);
        }
        if (isset($properties['old']['status'])) {
            $properties['old']['status'] = $this->statusToLabel($properties['old']['status']);
        }

        // مثلاً تبدیل product_id به نام محصول
        if (isset($properties['attributes']['product_id'])) {
            $productId = $properties['attributes']['product_id'];
            $product = Product::query()->firstWhere('id', $productId);
            if ($product ) {
                $properties['attributes']['product_id'] = $product->info;
            }elseif ($productId !=null){
                $properties['attributes']['product_id'] = $productId;
            }
        }

        if (isset($properties['old']['product_id'])) {
            $productId = $properties['old']['product_id'];
            $product = Product::query()->firstWhere('id', $productId);
            if ($product ) {
                $properties['old']['product_id']  = $product->info;
            }elseif ($productId !=null){
                $properties['old']['product_id']  = $productId;
            }
        }
        // تبدیل unit_id به نام واحد
        if (isset($properties['attributes']['unit_id'])) {
            $unitId = $properties['attributes']['unit_id'];
            $unit = Unit::find($unitId);
            if ($unit) {
                $properties['attributes']['unit_id'] = $unit->title;
            } elseif ($unitId !== null) {
                $properties['attributes']['unit_id'] = $unitId;
            }
        }
        if (isset($properties['old']['unit_id'])) {
            $unitId = $properties['old']['unit_id'];
            $unit = Unit::find($unitId);
            if ($unit) {
                $properties['old']['unit_id'] = $unit->title;
            } elseif ($unitId !== null) {
                $properties['old']['unit_id'] = $unitId;
            }
        }

// تبدیل project_id به نام پروژه
        if (isset($properties['attributes']['project_id'])) {
            $projectId = $properties['attributes']['project_id'];
            $project = Project::find($projectId);
            if ($project) {
                $properties['attributes']['project_id'] = $project->name;
            } elseif ($projectId !== null) {
                $properties['attributes']['project_id'] = $projectId;
            }
        }
        if (isset($properties['old']['project_id'])) {
            $projectId = $properties['old']['project_id'];
            $project = Project::find($projectId);
            if ($project) {
                $properties['old']['project_id'] = $project->name;
            } elseif ($projectId !== null) {
                $properties['old']['project_id'] = $projectId;
            }
        }


        // برگردوندن آرایه تغییر یافته به Collection
        $activity->properties = collect($properties);
    }



    protected function statusToLabel(string $status): string
    {

        return match ($status) {
            'approve' => 'Approved',
            'rejected' => 'Rejected',
            'Revise' => 'Revise',
            default => 'Pending',
        };
    }

    protected $casts=[
        'status'=>ItemStatus::class
    ];
    public function getLogAttribute(){
        return 'ATGT/UNC/'.$this?->purchaseRequest?->purchase_number.' Item '.' - '. $this?->product?->title;
    }

    public function getDepartmentAttribute(){
        return $this->product->department_id  ;
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }
    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class,'project_id');
    }
    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function currency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
    public function purchaseOrderItem(): \Illuminate\Database\Eloquent\Relations\hasOne
    {
        return $this->hasOne(PurchaseOrderItem::class);
    }
    public function quotationItems(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(QuotationItem::class);
    }
}
