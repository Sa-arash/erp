<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UrgentLeaveSecurityResource\Pages;
use App\Models\UrgentLeave;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UrgentLeaveSecurityResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = UrgentLeave::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-minus';
    protected static ?string $navigationGroup = 'Security Management';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }
    public static function canAccess(): bool
    {
        return auth()->user()->can('view_any_urgent::leave::security');
    }
    public static function canView(Model $record): bool
    {
        return true; // TODO: Change the autogenerated stub
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'reception',
        ];
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('id','desc')
            ->columns([
                Tables\Columns\TextColumn::make(getRowIndexName())->rowIndex(),
                Tables\Columns\TextColumn::make('employee.ID_number')->label('ID Number'),
                Tables\Columns\TextColumn::make('employee.fullName')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('date')->label('Date & Time')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('time_out')->label('Time OUT')->time()->sortable(),
                Tables\Columns\TextColumn::make('time_in')->label('Time IN')->time()->sortable(),
                Tables\Columns\TextColumn::make('hours')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('approvals.employee.fullName')->tooltip(fn($record)=>isset($record->approvals[0])? $record->approvals[0]?->approve_date:'')->label('Line Manager')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('approval_date')->label('Approve Date & Time')->dateTime()->sortable(),
                Tables\Columns\ImageColumn::make('approvals')->label('Process Status ')->state(function ($record) {
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
                //
            ])
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
                Tables\Actions\Action::make('pdf')->label('')->tooltip('Print')->icon('heroicon-s-printer')->size(ActionSize::Medium)
                    ->url(fn($record) => route('pdf.urgentleave',['id'=>$record->id]))->openUrlInNewTab(),
                Tables\Actions\Action::make('status')->visible(fn($record)=> auth()->user()->can('reception_urgent::leave::security') and $record?->status->value ==='accepted' )->color('warning')->form([
                    Forms\Components\ToggleButtons::make('status')->grouped()->options(['Returned'=>'Returned','Not Returned'=>'Not Returned'])->colors(['Returned'=>'success','Not Returned'=>'warning'])->required()
                ])->requiresConfirmation()->action(function ($data,$record){
                    $record->update(['status'=>$data['status']]);
                    sendSuccessNotification();
                }),
                Tables\Actions\Action::make('checkOUT')->label('Check OUT')->form([
                    Forms\Components\TimePicker::make('time')->seconds(false)->default(now())->required(),
                    Forms\Components\Textarea::make('description')->nullable()
                ])->requiresConfirmation()->action(function ($record,$data){
                    $record->update(['checkOUT'=>$data['time']]);
                    sendSuccessNotification();
                })->visible(fn($record)=> auth()->user()->can('reception_urgent::leave::security') and $record->status->value==="accepted" and $record->checkOUT ===null),
                Tables\Actions\Action::make('checkIN')->label('Check IN')->form([
                    Forms\Components\TimePicker::make('time')->seconds(false)->default(now())->required(),
                    Forms\Components\Textarea::make('description')->nullable()
                ])->requiresConfirmation()->action(function ($record,$data){
                    $record->update(['checkIN'=>$data['time']]);
                    sendSuccessNotification();
                })->visible(fn($record)=> auth()->user()->can('reception_urgent::leave::security') and $record->status->value==="accepted" and  $record->checkOUT !==null  and $record->checkIN ===null)
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUrgentLeaveSecurities::route('/'),
        ];
    }
}
