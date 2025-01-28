<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ApprovalResource\Pages;
use App\Filament\Admin\Resources\ApprovalResource\RelationManagers;
use App\Models\Approval;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Table;

class ApprovalResource extends Resource
{
    protected static ?string $model = Approval::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    public static function table(Table $table): Table
    {
        return $table->query(Approval::query()->where('employee_id', getEmployee()->id)->orderBy('id','desc'))
            ->columns([
                Tables\Columns\TextColumn::make('approvable_type')->state(function ($record) {
                    return substr($record->approvable_type, 11);
                })->searchable()->badge(),
                Tables\Columns\TextColumn::make('approvable_id')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('comment')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('approve_date')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('approve')->icon('heroicon-o-check-badge')->iconSize(IconSize::Large)->color('success')->form([
                    Forms\Components\ToggleButtons::make('status')->default('Approve')->colors(['Approve' => 'success', 'NotApprove' => 'danger', 'Pending' => 'primary'])->options(['Approve' => 'Approve', 'Pending' => 'Pending', 'NotApprove' => 'NotApprove'])->grouped(),
                    Forms\Components\Textarea::make('comment')->nullable()
                ])->action(function ($data, $record) {
                    $record->update(['comment'=>$data['comment'],'status' => $data['status'],'approve_date'=>now()]);
                    Notification::make('success')->success()->title($data['status'])->send();
                })->requiresConfirmation()->visible(fn($record) => $record->status->name === "Pending")
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    public static function getNavigationBadge(): ?string
    {
        return Approval::query()->where('employee_id', getEmployee()->id)->where('status','Pending')->count(); // TODO: Change the autogenerated stub
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
            'index' => Pages\ListApprovals::route('/'),
//            'create' => Pages\CreateApproval::route('/create'),
//            'edit' => Pages\EditApproval::route('/{record}/edit'),
        ];
    }
}
