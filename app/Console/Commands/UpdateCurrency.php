<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;

class UpdateCurrency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-currency';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = \Illuminate\Support\Facades\Http::get('https://sarafi.af/en/exchange-rates/sarai-shahzada');

        $html = $response->body();
dd(1);
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
        Company::query()->where('id',10)->update(['address'=>5]);

    }
}
