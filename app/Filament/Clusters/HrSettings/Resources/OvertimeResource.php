<?php

namespace App\Filament\Clusters\HrSettings\Resources;

use App\Enums\LeaveStatus;
use App\Filament\Clusters\HrSettings;
use App\Filament\Clusters\HrSettings\Resources\OvertimeResource\Pages;
use App\Filament\Clusters\HrSettings\Resources\OvertimeResource\RelationManagers;
use App\Models\Employee;
use App\Models\Overtime;
use App\Models\Typeleave;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class OvertimeResource extends Resource
{
    protected static ?string $model = Overtime::class;

    protected static ?string $navigationIcon = 'heroicon-s-folder-plus';
    protected static ?string $navigationGroup = 'HR Management';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')->label('Employee')->searchable()->preload()->options(Employee::query()->where('company_id',getCompany()->id)->pluck('fullName','id'))->required(),
                Forms\Components\TextInput::make('title')->label('description')->required()->maxLength(255),
                Forms\Components\DatePicker::make('overtime_date')->required(),
                Forms\Components\TextInput::make('hours')->numeric()->required()

              //  Forms\Components\Select::make('user_id')->relationship('user', 'name'),
             //   Forms\Components\DateTimePicker::make('approval_date'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.fullName')->sortable(),
                Tables\Columns\TextColumn::make('title')->label('description')->searchable(),
                Tables\Columns\TextColumn::make('hours')->label('Hours'),
                Tables\Columns\TextColumn::make('overtime_date')->date()->sortable(),
                // Tables\Columns\TextColumn::make('approval_date')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('status')->tooltip(fn($record)=>($record->status->value)==='Approve'?:$record->approval_date)->sortable()->badge(),
            ])

            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')->iconSize(IconSize::Medium)->color('success')
                ->icon(fn($record)=>($record->status->value) === 'accepted'?'heroicon-m-cog-8-tooth':'heroicon-o-check-badge')->label(fn($record)=>($record->status->value) === 'accepted'?'Change Status':'Approve')
                ->form(function ($record) {
                    return [
                        Forms\Components\Section::make([
                            Forms\Components\TextInput::make('title')->label('Description')->disabled()->default($record->title)->required()->maxLength(255),
                            Forms\Components\Select::make('employee_id')->disabled()->default($record->employee_id)->label('Employee')->searchable()->preload()->options(Employee::query()->where('company_id',getCompany()->id)->pluck('fullName','id'))->required(),
                            Forms\Components\DatePicker::make('overtime_date')->label('Date')->disabled()->default($record->overtime_date)->required()  ,
                            Forms\Components\TextInput::make('hours')->default($record->hours)->required()->disabled()


                        ])->columns(4),
                        Forms\Components\Section::make([
                            Forms\Components\Placeholder::make('Total Overtime('.now()->format('M').")")->content(function ()use($record){
                                $overtimes= Overtime::query()->where('employee_id',$record->employee_id)->whereBetween('overtime_date',[now()->startOfMonth(),now()->endOfMonth()])->where('status','accepted')->sum('hours');
                                return new HtmlString("<div style='font-size: 25px !important;'>  <span style='color: red;font-size: 25px !important;'>$overtimes</span> Hours </div>");
                            }),
                            Forms\Components\ToggleButtons::make('status')->default($record->status)->options(LeaveStatus::class)->inline()->required(),

                            Forms\Components\Textarea::make('comment')->nullable()

                        ])
                    ];
                }
                )->action(function ($data,$record) {

                    $record->update([
                        'comment'=>$data['comment'],
                        'status'=>$data['status']->value,
                        'approval_date'=>now(),
                        'user_id'=>auth()->id()
                    ]);
                   return Notification::make('approveOvertime')->title('Approved Overtime')->success()->send()->sendToDatabase(auth()->user(),true);
                })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return self::$model::query()->where('status', 'pending')->where('company_id', getCompany()->id)->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOvertimes::route('/'),
           // 'create' => Pages\CreateOvertime::route('/create'),
           // 'edit' => Pages\EditOvertime::route('/{record}/edit'),
        ];
    }
}
