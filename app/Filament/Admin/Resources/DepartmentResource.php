<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DepartmentResource\Pages;
use App\Filament\Admin\Resources\DepartmentResource\RelationManagers;
use App\Filament\Clusters\HrSettings;
use App\Filament\Resources\EmployeeResource;
use App\Models\Department;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Unique;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;
    protected static ?int $navigationSort = -2;
    protected static ?string $cluster = HrSettings::class;
    protected static ?string $label='Department';
    protected static ?string $pluralLabel=' Department';
    protected static ?string $navigationGroup = 'HR Management System';


    protected static ?string $navigationIcon = 'departement';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->label('Department Name')
                    ->required()
                    ->maxLength(255)->columnSpanFull()->unique(ignoreRecord: true,modifyRuleUsing: function (Unique $rule) {
                        return $rule->where('company_id', getCompany()->id);
                    }),
                Forms\Components\TextInput::make('abbreviation')->maxLength(10)->columnSpanFull() ->required(),
                Forms\Components\Textarea::make('description')->columnSpanFull(),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->searchable()
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('title')->label('Department Name')->sortable(),
                Tables\Columns\TextColumn::make('abbreviation')->label('Abbreviation')->sortable(),
                Tables\Columns\TextColumn::make('employee.fullName')->label('Line Manager ')->badge(),
                TextColumn::make('employees')->color('aColor')->alignCenter()->state(fn($record)=> $record->employees->count())->url(fn($record)=>EmployeeResource::getUrl().'?tableFilters[department_id][value]='.$record->id),


            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()->visible(fn($record)=>$record->employees->count()===0 and $record->products->count()===0  ),
                Tables\Actions\EditAction::make()->modelLabel('Edit '),
                Tables\Actions\Action::make('head')->icon('heroicon-m-user-circle')->iconSize(IconSize::Large)->tooltip('Set Head of Department')->label('Set Head of Department')->form(function ($record){
                    return [
                        Forms\Components\Select::make('employee_id')->label('Line Manager ')->searchable()->preload()->options(getCompany()->employees()->pluck('fullName','id'))->required()->default($record->employee_id)
                    ];
                })->action(function ($record,$data){
                    $record->update([
                        'employee_id'=>$data['employee_id']
                    ]);
                    Notification::make('success')->success()->title('Head of Department Successfully Assigned')->send()->sendToDatabase(auth()->user());
                })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepartments::route('/'),
//            'create' => Pages\CreateDepartment::route('/create'),
//            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }
}
