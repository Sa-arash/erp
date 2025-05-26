<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ITemployeeResource\Pages;
use App\Filament\Admin\Resources\ITemployeeResource\RelationManagers;
use App\Models\Employee;
use App\Models\ITemployee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Filament\Admin\Resources\EmployeeResource\RelationManagers\AssetEmployeeItemsRelationManager;
use App\Filament\Admin\Resources\EmployeeResource\RelationManagers\LeavesRelationManager;
use App\Filament\Admin\Resources\EmployeeResource\RelationManagers\OverTimesRelationManager;
use App\Filament\Admin\Resources\EmployeeResource\RelationManagers\PayrollsRelationManager;
use App\Models\Benefit;
use App\Models\CompanyUser;
use App\Models\Contract;
use App\Models\Department;
use App\Models\Duty;
use App\Models\Position;
use App\Models\Structure;
use App\Models\User;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Support\Enums\IconSize;
use Filament\Support\RawJs;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use OpenSpout\Common\Entity\Style\CellAlignment;
use Spatie\Permission\Models\Role;
class ITemployeeResource extends Resource
    implements HasShieldPermissions
{
    protected static ?string $model = Employee::class;

    protected static ?string $label='User Creation';
    protected static ?string $pluralLabel='Users';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'IT Management';
    protected static ?string $navigationIcon = 'heroicon-m-user-group';

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'create',
            'update',
            'viewAny',
            'user',
            'password',
            'role',
            'email',
            'disable',
        ];
    }
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }


    public static function table(Table $table): Table
    {
        return $table
            ->searchable()
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\ImageColumn::make('media.original_url')->state(function ($record) {
                    return $record->media->where('collection_name','images')->first()?->original_url;
             })->disk('public')->defaultImageUrl(fn($record) => $record->gender === "male" ? asset('img/user.png') : asset('img/female.png'))->alignLeft()->label('Profile Picture')->width(50)->height(50)->extraAttributes(['style' => 'border-radius:50px!important']),
                Tables\Columns\TextColumn::make('fullName')->sortable()->alignLeft()->searchable(),
                Tables\Columns\TextColumn::make('gender')->state(function($record){
                    if ($record->gender==="male"){
                        return "Male";
                    }elseif ($record->gender==="female"){
                        return "Female";
                    }else{
                        return  "Other";
                    }
                } )->alignLeft()->sortable(),
                Tables\Columns\TextColumn::make('phone_number')->alignLeft()->sortable()->searchable(),
                Tables\Columns\TextColumn::make('duty.title')->alignLeft()->numeric()->sortable()->searchable(),
                Tables\Columns\TextColumn::make('department.title')->alignLeft()->color('aColor')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('position.title')->alignLeft()->label('Position')->sortable(),
                Tables\Columns\TextColumn::make('user.created_at')->dateTime()->alignLeft()->label('Created User')->sortable(),
                //                Tables\Columns\TextColumn::make('payrolls_count')->counts('payrolls')->badge()->url(fn($record): string => (PayrollResource::getUrl('index', ['tableFilters[employee_id][value]' => $record->id])))->sortable(),

            ])
            ->filters([
                TernaryFilter::make('Have Account')->queries(
                    true: fn (Builder $query) => $query->whereHas('user',function ($query){
                        return $query->where('status',1);
                    }),
                    false: fn (Builder $query) =>$query->whereHas('user',function ($query){
                        return $query;
                    },0),
                    blank: fn (Builder $query) => $query,
                )->searchable()->default(1)->label('Have Account'),
                SelectFilter::make('department_id')->searchable()->preload()->options(Department::where('company_id', getCompany()->id)->get()->pluck('title', 'id'))
                    ->label('Department'),
                    SelectFilter::make('position_id')->searchable()->preload()->options(Position::where('company_id', getCompany()->id)->get()->pluck('title', 'id'))
                    ->label('Designation'),


                SelectFilter::make('duty_id')->searchable()->preload()->options(Duty::where('company_id', getCompany()->id)->get()->pluck('title', 'id'))
                    ->label('duty'),

                SelectFilter::make('gender')->options(['male'=>'Male','female'=>'Female','other'=>'Other'])->searchable()->preload(),

                DateRangeFilter::make('birthday'),
                DateRangeFilter::make('joining_date'),
                DateRangeFilter::make('leave_date'),



            ], getModelFilter())
            ->actions([

                Tables\Actions\EditAction::make(),
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\Action::make('pdf')->tooltip('Print')->icon('heroicon-s-printer')->iconSize(IconSize::Medium)->label('')
                //     ->url(fn($record) => route('pdf.employee', ['id' => $record->id]))->openUrlInNewTab(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('makeUser')->icon('heroicon-o-user-circle')->label('Create User')->form([
                        Forms\Components\Section::make('')->schema([
                            Forms\Components\Select::make('roles')->model(User::class)
                                ->options(Role::query()->where('company_id',getCompany()->id)->pluck('name','id'))
                                ->multiple()
                                ->preload()
                                ->searchable(),
                            Forms\Components\TextInput::make('email')->model(User::class)->email()->unique('users', 'email', ignoreRecord: true)->required()->maxLength(255),
                            Forms\Components\TextInput::make('password')->autocomplete(false)->hintAction(Forms\Components\Actions\Action::make('generate_password')->action(function (Forms\Set $set) {
                                $password = Str::password(8);
                                $set('password', $password);
                                $set('password_confirmation', $password);
                            }))->revealable()->required()->configure()->same('password_confirmation')->password(),
                            Forms\Components\TextInput::make('password_confirmation')->autocomplete(false)->revealable()->required()->password()
                        ])
                    ])->visible(fn($record) => (auth()->user()->can('user_i::temployee')  and $record->user === null))->action(function ($data,$record){
                        $roles = $data['roles'];
                        $rolesWithCompanyId = [];
                        foreach ($roles as $roleId) {
                            $rolesWithCompanyId[$roleId] = ['company_id' => getCompany()->id];
                        }

                        $user = User::query()->create([
                            'name' => $record->fullName,
                            'email' => $data['email'],
                            'password' => $data['password']
                        ]);
                        $user->roles()->attach($rolesWithCompanyId);
                        $record->update(['user_id'=>$user->id]);
                        CompanyUser::query()->create(['user_id'=>$record->user_id,'company_id'=>getCompany()->id]);
                        Notification::make('success')->success()->title('Submitted Successfully')->send();

                    }),
                    Tables\Actions\Action::make('setMail')->visible(fn($record) => $record->user and auth()->user()->can('email_i::temployee')  )->label('Modify Mail')->fillForm(function ($record) {
                        return [
                            'email' => $record->user->email
                        ];
                    })->form(function ($record) {
                        return [
                            Forms\Components\TextInput::make('email')->model(User::class)->email()->unique('users', 'email', ignorable: User::query()->firstWhere('id', $record->user_id), ignoreRecord: true)->required()->maxLength(255),
                        ];
                    })->action(function ($data, $record) {
                        $record->user->update(['email' => $data['email']]);
                        Notification::make('success')->success()->title('Submitted Successfully')->send();
                    })->requiresConfirmation()->icon('heroicon-c-at-symbol')->color('info'),
                    Tables\Actions\Action::make('setPassword')->visible(fn($record) => $record->user and auth()->user()->can('password_i::temployee') )->label('Reset Password')->form([
                        Forms\Components\TextInput::make('password')->required()->autocomplete(false)
                    ])->requiresConfirmation()->action(function ($record, $data) {
                        $record->user->update(['password' => $data['password']]);
                        Notification::make('success')->success()->title('Submitted Successfully')->send();
                    })->icon('heroicon-s-lock-closed')->color('warning'),
                    Tables\Actions\Action::make('setRole')->visible(fn($record) => $record->user and auth()->user()->can('role_i::temployee') )->fillForm(function ($record) {
                        return [
                            'roles' => $record->user->roles->where('company_id',getCompany()->id)->where('is_show',1)->pluck('id')->toArray()
                        ];
                    })->label('Assignee Role')->form([
                        Forms\Components\Select::make('roles')->pivotData(['company_id' => getCompany()->id])->options(getCompany()->roles->where('is_show',1)->pluck('name', 'id'))->multiple()->preload()->searchable(),
                    ])->requiresConfirmation()->action(function ($record, $data) {
                        $user = User::query()->firstWhere('id', $record->user_id);
                        $rolesWithCompanyId = [];

                        if ($user->roles->where('is_show',0)->first()){
                            $rolesWithCompanyId[$user->roles->where('is_show',0)->first()->id]=['company_id' => getCompany()->id];
                        }

                        foreach ($data['roles'] as $roleId) {
                            $rolesWithCompanyId[$roleId] = ['company_id' => getCompany()->id];
                        }
                        $user->roles()->sync($rolesWithCompanyId);
                        Notification::make('success')->success()->title('Submitted Successfully')->send();
                    })->icon('heroicon-s-shield-check')->color('danger'),
                    Tables\Actions\Action::make('disable')->visible(fn($record) => $record->user and auth()->user()->can('disable_i::temployee') )->label(fn($record)=>$record?->user?->status? 'Disable':"Enable")->form([
                    ])->requiresConfirmation()->action(function ($record) {

                        $record->user->update(['status' => !$record->user->status]);
                        Notification::make('success')->success()->title('Submitted Successfully')->send();
                    })->icon(fn($record)=>$record?->user?->status? 'heroicon-o-x-circle':'heroicon-s-check')->color('warning'),
                ])->color('warning'),

            ])->actionsColumnLabel('Actions')
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PayrollsRelationManager::class,
            LeavesRelationManager::class,
            OverTimesRelationManager::class,
            AssetEmployeeItemsRelationManager::class,
          //  RelationManagers\AssetEmployeeRepairRelationManager::class,
        ];
    }

    // public static function getPages(): array
    // {
    //     return [
    //         'index' => Pages\ListEmployees::route('/'),
    //         'create' => Pages\CreateEmployee::route('/create'),
    //         'edit' => Pages\EditEmployee::route('/{record}/edit'),
    //         'view' => Pages\ViewEmployee::route('/{record}'),
    //     ];
    // }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListITemployees::route('/'),
            'create' => Pages\CreateITemployee::route('/create'),
            'edit' => Pages\EditITemployee::route('/{record}/edit'),
        ];
    }
}
