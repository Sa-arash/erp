<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UrgentLeaveSecurityResource\Pages;
use App\Models\UrgentLeave;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
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
    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'reception',
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make(getRowIndexName())->rowIndex(),
                Tables\Columns\TextColumn::make('employee.ID_number')->label('ID Number'),
                Tables\Columns\TextColumn::make('employee.fullName')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('time_out')->label('Time OUT')->time()->sortable(),
                Tables\Columns\TextColumn::make('time_in')->label('Time IN')->time()->sortable(),
                Tables\Columns\TextColumn::make('hours')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('date')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('approvals.employee.fullName')->tooltip(fn($record)=>isset($record->approvals[0])? $record->approvals[0]?->approve_date:'')->label('Approve Head')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('approval_date')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('checkOUT')->label('Check OUT')->time()->sortable(),
                Tables\Columns\TextColumn::make('checkIN')->label('Check IN')->time()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
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
