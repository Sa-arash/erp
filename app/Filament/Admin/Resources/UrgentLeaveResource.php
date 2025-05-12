<?php

namespace App\Filament\Admin\Resources;

use App\Enums\LeaveStatus;
use App\Filament\Admin\Resources\UrgentLeaveResource\Pages;
use App\Filament\Admin\Resources\UrgentLeaveResource\RelationManagers;
use App\Models\Employee;
use App\Models\Leave as ModelLeave;
use App\Models\Typeleave;
use App\Models\UrgentLeave;
use App\Models\UrgentTypeleave;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class UrgentLeaveResource extends Resource  implements HasShieldPermissions
{
    protected static ?string $model = UrgentLeave::class;

    protected static ?string $navigationGroup = 'HR Management System';
    protected static ?int $navigationSort = 3;
    protected static ?string $pluralLabel = "Urgent Leave ";
    protected static ?string $label = "Urgent Leave";
    protected static ?string $navigationIcon = 'heroicon-o-folder-minus';

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'admin'
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
              Forms\Components\Section::make([
                  Forms\Components\DateTimePicker::make('date')->default(now())->required(),
                  Forms\Components\Select::make('employee_id')->afterStateUpdated(function ($state,Set $set){
                      $employee=  Employee::query()->firstWhere('id',$state);
                      if ($employee){
                          $set('number',$employee->ID_number);
                      }
                  })->label('Employee')->required()->live()->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id'))->searchable()->preload(),
                   Forms\Components\TextInput::make('number')->disabled()->label('Badge Number'),
              ])->columns(3),
                Forms\Components\Section::make([
                    Forms\Components\TimePicker::make('time_out')->before('time_in')->seconds(false)->reactive()
                        ->afterStateUpdated(function (Set $set, $state) {
                            $set('time_in', $state);
                        })
                        ->required(),
                    Forms\Components\TimePicker::make('time_in')
                        ->after('time_out')
                        ->seconds(false),

                    Forms\Components\TextInput::make('hours')->numeric()
                        ->reactive()
                        ->afterStateUpdated(function (Set $set, Get $get, $state) {
                            if ($get('time_out')) {
                                $timeOut = \Carbon\Carbon::parse($get('time_out'));
                                $hoursToAdd = $state;
                                if ($hoursToAdd) {
                                    // dd($hoursToAdd);
                                    $newTimeIn = $timeOut->addHours((int)$hoursToAdd);
                                    $set('time_in', $newTimeIn->format('H:i')); // فرمت زمان را تنظیم کنید
                                }
                            }
                        }),
                ])->columns(3),
                Forms\Components\Textarea::make('reason')->columnSpanFull(),

            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.fullName')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('time_out')->time()->sortable(),
                Tables\Columns\TextColumn::make('time_in')->time()->sortable(),
                Tables\Columns\TextColumn::make('hours')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('date')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('approval_date')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
//                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('pdf')->label('PDF')->tooltip('Print')->icon('heroicon-s-printer')->size(ActionSize::Medium)
                    ->url(fn($record) => route('pdf.urgentleave',['id'=>$record->id]))->openUrlInNewTab(),
                Tables\Actions\Action::make('approve')->iconSize(IconSize::Medium)->color('success')
                    ->icon(fn($record)=>($record->status->value) === 'accepted'?'heroicon-m-cog-8-tooth':'heroicon-o-check-badge')->label(fn($record)=>($record->status->value) === 'accepted'?'Change Status':'Approve')
                    ->form(function ($record) {
                        return [
                            Forms\Components\ToggleButtons::make('status')->colors(['accepted'=>'success','rejected'=>'danger'])->grouped()->options(['accepted'=>'Approve','rejected'=>'Reject'])->inline()->required(),

                        ];
                    }
                    )->requiresConfirmation()->visible(fn($record)=>$record->admin_id ===null and auth()->user()->can('admin_urgent::leave'))->action(function ($data,$record) {

                        $record->update([
                            'status'=>$data['status'],
                            'approval_date'=>now(),
                            'admin_id'=>getEmployee()->id
                        ]);
                        Notification::make('approveLeave')->title('Approved Urgent Leave Employee:'.$record->employee->fullName)->success()->send()->sendToDatabase(auth()->user(),true);
                    })->visible(fn($record)=>$record->status->value=="approveHead")
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
            'index' => Pages\ListUrgentLeaves::route('/'),
            'create' => Pages\CreateUrgentLeave::route('/create'),
            'edit' => Pages\EditUrgentLeave::route('/{record}/edit'),
        ];
    }
}
