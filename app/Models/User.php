<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Namu\WireChat\Models\Conversation;
use Namu\WireChat\Traits\Chatable;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\HasRoles;
use TomatoPHP\FilamentMediaManager\Traits\InteractsWithMediaFolders;

class User extends Authenticatable implements HasTenants, FilamentUser, HasName, HasAvatar
{
    use HasRoles, Notifiable;
    use HasFactory;
    use InteractsWithMediaFolders;
    use Chatable;
    public function canCreateGroups(): bool
    {
        return  true;
    }
    public function getCoverUrlAttribute(): ?string
    {
        return $this->employee?->media->where('collection_name','images')->first()?->original_url ?? null;
    }


    public function canCreateChats(): bool
    {
        return true;
    }
    public function getLogAttribute(){
        return $this?->name."#-#".$this?->email;
    }
    public function getFilamentName(): string
    {
        return $this->employee->fullName ?? $this->name;
    }

    public function getFilamentAvatarUrl(): ?string
    {

        return  getEmployee()?->media->where('collection_name','images')->first()?->original_url;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId()==="super-admin" and $this->is_super ===1 ){
            return  true;
        }elseif ($panel->getId()==="admin" and $this->status==1){
            return  true;
        }else{
            return  false;
        }
    }

    public function allRoles(): BelongsToMany
    {
        $relation = $this->morphToMany(
            config('permission.models.role'),
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.model_morph_key'),
            app(PermissionRegistrar::class)->pivotRole
        );

            return $relation;



    }
    public function employee(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function teams(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_users');
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->teams;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->teams()->whereKey($tenant)->exists();
    }
    /** @use HasFactory<\Database\Factories\UserFactory> */

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_super',
        'need_new_password',
        'status'

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function companies(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_users');
    }
}
