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
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

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
                    Forms\Components\TimePicker::make('time_out')->before(function (Get $get){
                        if ($get('time_in')){
                            return $get('time_in');
                        }
                        return false;
                    })->seconds(false)->reactive()
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
        return $table->defaultSort('id','desc')
            ->columns([
                Tables\Columns\TextColumn::make(getRowIndexName())->rowIndex(),
                Tables\Columns\TextColumn::make('employee.ID_number')->label('ID Number'),
                Tables\Columns\TextColumn::make('employee.fullName')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('time_out')->label('Time OUT')->time()->sortable(),
                Tables\Columns\TextColumn::make('time_in')->label('Time IN')->time()->sortable(),
                Tables\Columns\TextColumn::make('hours')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('date')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('approvals.employee.fullName')->tooltip(fn($record)=>isset($record->approvals[0])? $record->approvals[0]?->approve_date:'')->label('Line Manager')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('approval_date')->dateTime()->sortable(),
                Tables\Columns\ImageColumn::make('approvals')->state(function ($record) {
                    $data = [];

                        $data[]=$record->employee->media->where('collection_name', 'images')->first()?->original_url;

                    foreach ($record->approvals as $approval) {
                        if ($approval->status->value == "Approve") {
                            if ($approval->employee->media->where('collection_name', 'images')->first()?->original_url) {
                                $data[] = $approval->employee->media->where('collection_name', 'images')->first()?->original_url;
                            } else {
                                $data[] = $approval->employee->gender === "male" ? asset('img/user.png') : asset('img/female.png');
                            }
                        }
                    }
                    if ($record->admin){
                        $data[]=$record->admin->media->where('collection_name', 'images')->first()?->original_url;
                    }
                    return $data;
                })->circular()->stacked(),
                Tables\Columns\TextColumn::make('checkOUT')->label('Check OUT')->time()->sortable(),
                Tables\Columns\TextColumn::make('checkIN')->label('Check IN')->time()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department')->searchable()->preload()->label('Department')->options(getCompany()->departments()->pluck('title','id'))->query(fn($query,$data)=>isset($data['value'])? $query->whereHas('employee',function ($query)use($data){
                    return $query->where('department_id',$data['value']);
                }):$query),
                Tables\Filters\SelectFilter::make('employee_id')->searchable()->preload()->label('Employee')->options(getCompany()->employees()->pluck('fullName','id')),
                DateRangeFilter::make('date')
            ],getModelFilter())
            ->actions([
                Tables\Actions\ViewAction::make()->infolist([
                    Grid::make(3)->schema([
                        TextEntry::make('employee.fullName')->label('Full Name'),
                        TextEntry::make('employee.ID_number')->label('ID Number'),
                        TextEntry::make('date')->label('Date & Time')->dateTime(),
                        TextEntry::make('time_in')->label('Time IN')->time(),
                        TextEntry::make('time_out')->label('Time OUT')->time(),
                        TextEntry::make('hours')->label('Total Hours'),
                        TextEntry::make('checkIN')->label('Check IN')->time(),
                        TextEntry::make('checkOUT')->label('Check OUT')->time(),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('approval_date')->label('Approve Date')->dateTime(),
                        TextEntry::make('approvals.0.employee.fullName')->label('Line Manager'),
                    ]),

                    Group::make([
                        ImageEntry::make('employee.media.0.original_url')->circular()->label('Employee'),
                        ImageEntry::make('approvals.0.employee.media.0.original_url')->circular()->label('Line Manager'),
                        ImageEntry::make('admin.media.0.original_url')->circular()->label('Admin'),
                    ])->columns(3),
                ]),
//                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('pdf')->label('')->tooltip('Print')->icon('heroicon-s-printer')->size(ActionSize::Medium)
                    ->url(fn($record) => route('pdf.urgentleave',['id'=>$record->id]))->openUrlInNewTab(),
                Tables\Actions\Action::make('approve')->iconSize(IconSize::Medium)->color('success')
                    ->icon(fn($record)=>($record->status->value) === 'accepted'?'heroicon-m-cog-8-tooth':'heroicon-o-check-badge')->label(fn($record)=>($record->status->value) === 'accepted'?'Change Status':'Approve')
                    ->form(function () {
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
    public static function getNavigationBadge(): ?string
    {
        return UrgentLeave::query()->where('company_id',getCompany()->id)->where('admin_id',null)->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUrgentLeaves::route('/'),
            'create' => Pages\CreateUrgentLeave::route('/create'),
//            'edit' => Pages\EditUrgentLeave::route('/{record}/edit'),
        ];
    }
}
