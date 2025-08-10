<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    /** @use HasFactory<\Database\Factories\ProductCategoryFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    public function getLogAttribute()
    {
        return $this?->title . "#-#" . $this?->account->code;
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getSiblingIndex(): int
    {
        $query = static::where('company_id', $this->company_id)
            ->where('parent_id', $this->parent_id)
            ->orderBy('title'); // یا created_at

        $siblings = $query->pluck('id')->values();

        return $siblings->search($this->id) + 1;
    }

    public function generateCodeFromParent(): string
    {
        if ($this->parent_id === null) {
            // سطح اول: پیدا کردن تعداد root categories
            $count = ProductCategory::where('company_id', $this->company_id)
                ->whereNull('parent_id')
                ->count();

            return str_pad($count + 1, 2, '0', STR_PAD_LEFT); // مثل 01، 02، 03
        }
    }

    public function generateNextChildCode(): string
    {
        if (!$this->code) {
            throw new \Exception('Parent must have a code to generate child code.');
        }

        $children = ProductCategory::where('parent_id', $this->id)
            ->where('company_id', $this->company_id)
            ->pluck('code');

        $maxSuffix = 0;

        foreach ($children as $childCode) {
            $suffix = intval(substr($childCode, strlen($this->code)));
            if ($suffix > $maxSuffix) {
                $maxSuffix = $suffix;
            }
        }

        // ساخت کد جدید
        $newSuffix = str_pad($maxSuffix + 1, 2, '0', STR_PAD_LEFT);
        return $this->code . $newSuffix;
    }

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }

    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Product::class, 'product_category_id');
    }

}
