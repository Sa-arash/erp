<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CurrencyResource\Pages;
use App\Filament\Admin\Resources\CurrencyResource\RelationManagers;
use App\Filament\Clusters\FinanceSettings;
use App\Models\Currency;
use App\Models\FinancialPeriod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Unique;

class CurrencyResource extends Resource
{
    protected static ?string $model = Currency::class;
    protected static ?string $cluster = FinanceSettings::class;
    protected static ?string $navigationGroup = 'Finance Management';
    protected static ?string $label = 'Currency';
    protected static ?int $navigationSort=4;
    protected static ?string $navigationIcon = 'heroicon-c-currency-dollar';

    public static function getCluster(): ?string
    {
        $period = FinancialPeriod::query()->where('company_id', getCompanyUrl())->where('status', 'During')->first();
        if ($period) {
            return parent::getCluster();
        }
        return '';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('symbol')->required()->maxLength(255),
                Forms\Components\TextInput::make('exchange_rate')->required()->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                Forms\Components\ToggleButtons::make('is_company_currency')->unique(ignoreRecord: true,modifyRuleUsing: function (Unique $rule) {
                    return $rule->where('is_company_currency', 1)->where('company_id',getCompany()->id);
               })->grouped()->label('Base Currency')->default(0)->boolean('Yes','No')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {

        return $table
            ->columns([

                Tables\Columns\TextColumn::make('id')->searchable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('symbol')->searchable(),
                Tables\Columns\TextColumn::make('exchange_rate')->numeric()->sortable()->label('Exchange Rate'),
                Tables\Columns\IconColumn::make('is_company_currency')->boolean()->label('Base Currency'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('online')->label('Online Price')->fillForm(function ($record){
                    return [
                        'online_currency'=>$record->online_currency,
                        'exchange_rate'=>$record->exchange_rate
                    ];
                })->form([
                    Forms\Components\Placeholder::make('content')
                        ->content(function () {
                            $response = Http::get('https://sarafi.af/en/exchange-rates/sarai-shahzada');
                            $html = $response->body();

                            libxml_use_internal_errors(true);
                            $doc = new \DOMDocument();
                            $doc->loadHTML($html);
                            $xpath = new \DOMXPath($doc);

                            $rows = $xpath->query('//table//tr');

                            $usdRate = [];

                            foreach ($rows as $row) {
                                if (str_contains($row->textContent, 'USD - US Dollar') ||
                                    str_contains($row->textContent, 'GBP - British Pound') ||
                                    str_contains($row->textContent, 'EUR - Euro') ||
                                    str_contains($row->textContent, 'PKR - Pakistani Rupee 1K') ||
                                    str_contains($row->textContent, 'JPY - Japanese Yen 1K') ||
                                    str_contains($row->textContent, 'INR - Indian Rupee 1K') ||
                                    str_contains($row->textContent, 'IRR - Iranian Rial 1K')) {

                                    $cols = $row->getElementsByTagName('td');
                                    if ($cols->length >= 3) {
                                        $usdRate[] = [
                                            'currency' => trim($cols[0]->textContent),
                                            'buy' => trim($cols[1]->textContent),
                                            'sell' => trim($cols[2]->textContent),
                                        ];
                                    }
                                }
                            }

                            // ساخت HTML جدول
                            $table = '<table style="width:100%; border-collapse: collapse;border: 1px solid black" >';
                            $table .= '<thead><tr><th  style="text-align: center;border: 1px solid black">Currency</th><th style="text-align: center;border: 1px solid black">Buy</th><th style="text-align: center;border: 1px solid black">Sell</th></tr></thead><tbody>';

                            foreach ($usdRate as $rate) {
                                $table .= "<tr>
                <td style='text-align: center;border: 1px solid black'>{$rate['currency']}</td>
                <td style='text-align: center;border: 1px solid black'>{$rate['buy']}</td>
                <td style='text-align: center;border: 1px solid black'>{$rate['sell']}</td>
            </tr>";
                            }

                            $table .= '</tbody></table>';

                            return New HtmlString($table);
                        })
                        ->columnSpanFull(),
                    Forms\Components\Select::make('online_currency')->label('Online Currency')->options(function () {
                        $response = \Illuminate\Support\Facades\Http::get('https://sarafi.af/en/exchange-rates/sarai-shahzada');

                        $html = $response->body();

                        libxml_use_internal_errors(true);

                        $doc = new \DOMDocument();
                        $doc->loadHTML($html);
                        $xpath = new \DOMXPath($doc);

                        $rows = $xpath->query('//table//tr');

                        $usdRate = [];

                        foreach ($rows as $row) {
                            if (str_contains($row->textContent, 'USD - US Dollar') or str_contains($row->textContent, 'GBP - British Pound')or str_contains($row->textContent, 'EUR - Euro')or str_contains($row->textContent, 'PKR - Pakistani Rupee 1K') or str_contains($row->textContent, 'JPY - Japanese Yen 1K') or str_contains($row->textContent, 'INR - Indian Rupee 1K')or str_contains($row->textContent, 'IRR - Iranian Rial 1K') ) {
                                $cols = $row->getElementsByTagName('td');
                                $usdRate[trim($cols[0]->textContent)] = trim($cols[0]->textContent);
                                if (count($usdRate) ==9){
                                    break;
                                }
                            }
                        }
                        return $usdRate;
                    })->searchable(),
                    Forms\Components\TextInput::make('exchange_rate')->required()->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),

                ])->action(function ($record,$data){
                    $record->update(['online_currency'=>$data['online_currency'],'exchange_rate'=>$data['exchange_rate']]);
                    sendSuccessNotification();
                }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->visible(fn($record)=>(
                     $record->accounts->isEmpty()&&
                $record->transactions->isEmpty()&&
                $record->parties->isEmpty()&&
                $record->purchaseRequest->isEmpty()&&
                $record->banks->isEmpty()))
            ])
            ->bulkActions([

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
            'index' => Pages\ListCurrencies::route('/'),
        ];
    }
}
