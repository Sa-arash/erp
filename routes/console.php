<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());


})->purpose('Display an inspiring quote')->everyFiveMinutes();
Artisan::command('app:update-currency', function () {
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
            $usdRate[trim($cols[0]->textContent)] = [
                'buy' => trim($cols[1]->textContent),
                'sell' => trim($cols[2]->textContent),
            ];

            \App\Models\Currency::query()->where('online_currency',trim($cols[0]->textContent))->update(['exchange_rate'=>$usdRate[trim($cols[0]->textContent)]['sell']]);

            if (count($usdRate) ==9){
                break;
            }
        }
    }
    $this->info('Currency updated!');
})->describe('Update the currency.');
