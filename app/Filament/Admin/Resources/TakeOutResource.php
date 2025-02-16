<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TakeOutResource\Pages;
use App\Filament\Admin\Resources\TakeOutResource\RelationManagers;
use App\Models\Employee;
use App\Models\TakeOut;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class TakeOutResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = TakeOut::class;

    protected static ?string $navigationIcon = 'heroicon-c-arrow-up-tray';
    protected static ?string $navigationGroup = 'Security Management';
    protected static ?int $navigationSort = 99;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'reception',
        ];
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('id','desc')
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('employee.fullName'),
                Tables\Columns\TextColumn::make('assets.product.title')->state(fn($record)=> $record->assets->pluck('title')->toArray())->badge()->label('Assets'),
                Tables\Columns\TextColumn::make('itemsOut')->state(function($record){
                    $data=[];
                    if ($record->itemsOut){
                        foreach ($record->itemsOut as $item){
                            $data[]=$item['name'];
                        }
                    }
                    return $data;
                })->limitList(5)->badge(),
                Tables\Columns\TextColumn::make('from'),
                Tables\Columns\TextColumn::make('to'),
                Tables\Columns\TextColumn::make('date')->date(),
                Tables\Columns\TextColumn::make('return_date')->date(),
                Tables\Columns\TextColumn::make('mood')->color(function($state){
                    switch ($state){
                        case "Approved":
                            return 'success';
                        case "Pending":
                            return 'info';
                        case "NotApproved":
                            return 'danger';
                    }
                })->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('gate_status')->label('Gate Status')->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee_id')->options(Employee::query()->where('company_id',getCompany()->id)->pluck('fullName','id')),
                DateRangeFilter::make('date')->label('Date'),
            ],getModelFilter())
            ->actions([
                Tables\Actions\Action::make('ActionOutSide')->label(' CheckOut')->form([
                    Forms\Components\DateTimePicker::make('OutSide_date')->withoutSeconds()->label(' Date And Time')->required()->default(now()),
                    Forms\Components\Textarea::make('OutSide_comment')->label(' Comment')
                ])->requiresConfirmation()->action(function ($data, $record) {
                    $record->update(['OutSide_date' => $data['OutSide_date'], 'OutSide_comment' => $data['OutSide_comment'],'gate_status'=>'CheckedOut']);
                    Notification::make('success')->success()->title('Submitted Successfully')->send();
                })->visible(function ($record) {
                    if (auth()->user()->can('reception_take::out')) {
                        if ($record->mood === "Approved") {
                            if ($record->OutSide_date === null) {
                                return true;
                            }
                        }
                    }
                    return false;
                }),
                Tables\Actions\Action::make('ActionInSide')->label('CheckIn')->form([
                    Forms\Components\DateTimePicker::make('InSide_date')->withoutSeconds()->label(' Date And Time')->required()->default(now()),
                    Forms\Components\Textarea::make('inSide_comment')->label(' Comment')
                ])->requiresConfirmation()->action(function ($data, $record) {
                    $record->update(['InSide_date' => $data['InSide_date'], 'inSide_comment' => $data['inSide_comment'],'gate_status'=>'CheckedIn']);
                    Notification::make('success')->success()->title('Submitted Successfully')->send();
                })->visible(function($record){
                    if ($record->status !=="Non-Returnable"){
                        if (auth()->user()->can('reception_take::out')){
                            if ($record->InSide_date!==null ){
                                return false;
                            }
                            if ($record->OutSide_date !==null ){
                                return true;
                            }
                        }
                    }
                    return  false;
                }),
                Tables\Actions\ViewAction::make('view')->infolist([
                    Section::make([
                        TextEntry::make('employee.fullName'),
                        TextEntry::make('from'),
                        TextEntry::make('to'),
                        TextEntry::make('date')->date(),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('type')->badge(),
                        RepeatableEntry::make('items')->label('Assets')->schema([
                            TextEntry::make('asset.title'),
                            TextEntry::make('remarks'),
                            TextEntry::make('returned_date'),
                        ])->columnSpanFull()->columns(3),
                        RepeatableEntry::make('itemsOut')->label('itemsOut')->schema([
                            TextEntry::make('name'),
                            TextEntry::make('remarks'),
                        ])->columnSpanFull()->columns(),
                        \Filament\Infolists\Components\Section::make([
                            TextEntry::make('OutSide_date')->dateTime(),
                            TextEntry::make('OutSide_comment'),
                        ])->columns(),
                        \Filament\Infolists\Components\Section::make([
                            TextEntry::make('InSide_date')->dateTime(),
                            TextEntry::make('inSide_comment'),
                        ])->columns(),
                    ])->columns()
                ]),
                Tables\Actions\Action::make('pdf')->tooltip('Print')->icon('heroicon-s-printer')->iconSize(IconSize::Medium)->label('')
                    ->url(fn($record) => route('pdf.takeOut', ['id' => $record->id]))->openUrlInNewTab(),
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
            'index' => Pages\ListTakeOuts::route('/'),
        ];
    }
}
