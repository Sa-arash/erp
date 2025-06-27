<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ServiceResource\Pages;
use App\Filament\Admin\Resources\ServiceResource\RelationManagers;
use App\Models\Asset;
use App\Models\AssetEmployeeItem;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use TomatoPHP\FilamentMediaManager\Form\MediaManagerInput;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;
    protected static ?string $navigationGroup = 'Logistic Management';
    protected static ?int $navigationSort = 9;
    protected static ?string $label='Maintenance';

  protected static ?string $navigationIcon = 'heroicon-m-wrench-screwdriver';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Service Request')->schema([
                    Forms\Components\Select::make('employee_id')->label('Employee')->live()
                        ->searchable()
                        ->preload()
                        ->required()
                        ->options(getCompany()->employees->pluck('fullName', 'id'))
                        ->default(fn() => auth()->user()->employee->id),


                    Select::make('asset_id')
                        ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                        ->live()->label('Asset')->options(function (Get $get) {
                            if ($get('employee_id')) {

                                $employeeID = $get('employee_id');
                                $data = [];



                                $assets = Asset::query()->with('product')->where('check_out_to',$employeeID)->where('company_id', getCompany()->id)->get();
                                foreach ($assets as $asset) {
                                    $data[$asset->id] = $asset->product?->title . " ( SKU #" . $asset->product?->sku . " ) ".$asset->description;
                                }
                                return $data;
                            }
                        })->required()->searchable()->preload(),

                    Forms\Components\DatePicker::make('request_date')
                        ->required(),
                        ToggleButtons::make('type')->options(['On-site Service' => 'On-site Service', 'Purchase Order' => 'Purchase Order', 'TakeOut For Reaper' => 'TakeOut For Reaper',])->inline(),
                        ToggleButtons::make('status')->options(['Complete' => 'Complete', 'Canceled' => 'Canceled' ])->default('Complete')->inline(),
                    MediaManagerInput::make('images')->orderable(false)
                        ->disk('public')
                        ->schema([
                        ])->maxItems(1),
                    Forms\Components\DatePicker::make('answer_date'),
                    Forms\Components\DatePicker::make('service_date'),
                    Forms\Components\Textarea::make('note')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('reply')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('PO_number')->label('PONO')
                        ->maxLength(255),
                    Forms\Components\Hidden::make('company_id')
                        ->default(getCompany()->id)
                        ->required(),

                ])->columns(3),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('id','desc')
            ->columns([
                Tables\Columns\TextColumn::make(getRowIndexName())->rowIndex(),
                Tables\Columns\TextColumn::make('employee.fullName')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('request_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('asset.titlen')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('answer_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')->badge()->label('Type Of Service'),
                Tables\Columns\TextColumn::make('service_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('PO_number')->label('PONO')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reply')->limit(20)->tooltip(fn($record) => $record->reply)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('Action')->visible(fn($record) => $record->status === 'Pending')->form([
                    Group::make()->schema([
                    ToggleButtons::make('type')->options(['On-site Service' => 'On-site Service', 'Purchase Order' => 'Purchase Order', 'TakeOut For Repair' => 'TakeOut For Repair',])->inline()->required(),
                    Forms\Components\DatePicker::make('answer_date')->label('Answer Date')->default(now())->required(),
                    ])->columns(2)

                ])->action(function ($data, $record) {
                    $record->update([
                        'type' => $data['type'],
                        'answer_date' => $data['answer_date'],
                        'status' => 'In Progress',
                    ]);
                    $record->asset()->update(['status' => 'underRepair']);
                }),
                Tables\Actions\Action::make('Finish')->visible(fn($record) => $record->status === 'In Progress')->form([
                    Group::make()->schema([
                        ToggleButtons::make('status')->options(['Complete' => 'Complete', 'Canceled' => 'Canceled' ])->default('Complete')->inline()->required(),
                        Forms\Components\DatePicker::make('service_date')->default(now()),
                        Forms\Components\TextInput::make('PO_number')->label('PONO')
                        ->maxLength(255),
                        Forms\Components\Textarea::make('reply')
                        ->columnSpanFull(),
                    ])->columns(3)
                ])->action(function ($data, $record) {
                   ;
                    $record->update([
                        'service_date' => $data['service_date'],
                        'PO_number' => $data['PO_number'],
                        'reply' => $data['reply'],
                        'status' => $data['status'],
                    ]);
                    if ($data['status']==="Complete"){
                        $record->asset()->update(['status' => 'inuse']);
                    }else{
                        $record->asset()->update(['status' => 'underRepair']);
                    }
                }),

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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
