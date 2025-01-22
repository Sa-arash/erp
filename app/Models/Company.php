<?php

namespace App\Models;

use Database\Factories\ProductCategoryFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Spatie\Permission\Models\Role;

class Company extends Model implements HasAvatar, HasName
{
    use HasFactory;

    public function getFilamentName(): string
    {
        return $this->title;
    }

    protected $fillable = ['category_account','customer_account', 'vendor_account', 'account_bank', 'weekend_days', 'daily_working_hours', 'overtime_rate', 'title', 'logo', 'description', 'user_id', 'country', 'address', 'contact_information', 'company_registration_document', 'currency'];

    protected $casts = [
        'weekend_days' => 'array',
    ];

    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->logo) {
            return asset('images/' . $this->logo);
        }
        return 'default';
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function departments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function accounts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Account::class);
    }


    public function customers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function vendors(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Vendor::class);
    }

    public function units(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function bankCategories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Bank_category::class);
    }

    public function productCategories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function subProductCategories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductSubCategory::class);
    }

    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Product::class);
    }
    public function purchaseRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseRequest::class);
    }
    public function purchaseRequestItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function incomes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Income::class);
    }

    public function expenses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Expense::class);
    }


    public function positions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function duties(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Duty::class);
    }

    public function benefits(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Benefit::class);
    }

    public function employees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function structures(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Structure::class);
    }

    public function warehouses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Warehouse::class);
    }

    public function benefit_employees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BenefitEmployee::class);
    }

    public function typeleaves(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Typeleave::class);
    }

    public function leaves(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function loans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function payrolls(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function contracts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function vendorTypes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VendorType::class);
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_users');
    }

    public function accountTypes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AccountType::class);
    }

    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function financialPeriods(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FinancialPeriod::class);
    }

    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function holidays(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Holiday::class);
    }

    public function overtimes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Overtime::class);
    }

    public function notifactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DatabaseNotification::class);
    }


    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(\App\Models\Inventory::class);
    }

    public function banks(): HasMany
    {
        return $this->hasMany(Bank::class);
    }

    public function cheques(): HasMany
    {
        return $this->hasMany(Cheque::class);
    }

    public function parties(): HasMany
    {
        return $this->hasMany(Parties::class);
    }

    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class);
    }

    public function warehouse(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function assetEmployees(): HasMany
    {
        return $this->hasMany(AssetEmployee::class);
    }
    public function assetEmployeeItems(): HasMany
    {
        return $this->hasMany(AssetEmployeeItem::class);
    }
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }


}
