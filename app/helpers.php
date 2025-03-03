<?php


use App\Models\Currency;
use App\Models\Employee;
use App\Models\FinancialPeriod;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Section;
use Filament\Notifications\Notification;
use Filament\Support\RawJs;

function getCompany(): ?\Illuminate\Database\Eloquent\Model
{
    return \Filament\Facades\Filament::getTenant();
}

function genderOption(): array
{
    return [
        "0" => "Woman",
        "1" => "Man",
    ];
}

function SumOfReport1($type, $creditor, $debtor)
{
    if ($type == "creditor") {
        return $creditor - $debtor;

    } elseif ($type == "debtor") {
        return $debtor - $creditor;
    }
}

function SumOfReport($record): int
{
    if ($record->type == "creditor") {
        return ($record->transactions->sum('creditor') - $record->transactions->sum('debtor'));

    } elseif ($record->type == "debtor") {
        return ($record->transactions->sum('debtor') - $record->transactions->sum('creditor'));
    }

}


function getCountry(): array
{
    return [
        "Afghanistan" => "Afghanistan",
        "Albania" => "Albania",
        "Algeria" => "Algeria",
        "American Samoa" => "American Samoa",
        "Andorra" => "Andorra",
        "Angola" => "Angola",
        "Anguilla" => "Anguilla",
        "Antarctica" => "Antarctica",
        "Antigua and Barbuda" => "Antigua and Barbuda",
        "Argentina" => "Argentina",
        "Armenia" => "Armenia",
        "Aruba" => "Aruba",
        "Australia" => "Australia",
        "Austria" => "Austria",
        "Azerbaijan" => "Azerbaijan",
        "Bahamas" => "Bahamas",
        "Bahrain" => "Bahrain",
        "Bangladesh" => "Bangladesh",
        "Barbados" => "Barbados",
        "Belarus" => "Belarus",
        "Belgium" => "Belgium",
        "Belize" => "Belize",
        "Benin" => "Benin",
        "Bermuda" => "Bermuda",
        "Bhutan" => "Bhutan",
        "Bolivia" => "Bolivia",
        "Bosnia and Herzegovina" => "Bosnia and Herzegovina",
        "Botswana" => "Botswana",
        "Bouvet Island" => "Bouvet Island",
        "Brazil" => "Brazil",
        "British Antarctic Territory" => "British Antarctic Territory",
        "British Indian Ocean Territory" => "British Indian Ocean Territory",
        "British Virgin Islands" => "British Virgin Islands",
        "Brunei" => "Brunei",
        "Bulgaria" => "Bulgaria",
        "Burkina Faso" => "Burkina Faso",
        "Burundi" => "Burundi",
        "Cambodia" => "Cambodia",
        "Cameroon" => "Cameroon",
        "Canada" => "Canada",
        "Canton and Enderbury Islands" => "Canton and Enderbury Islands",
        "Cape Verde" => "Cape Verde",
        "Cayman Islands" => "Cayman Islands",
        "CF" => "Central African Republic",
        "Central African Republic" => "Chad",
        "Chile" => "Chile",
        "China" => "China",
        "Christmas Island" => "Christmas Island",
        "Cocos [Keeling] Islands" => "Cocos [Keeling] Islands",
        "Colombia" => "Colombia",
        "Comoros" => "Comoros",
        "Congo - Brazzaville" => "Congo - Brazzaville",
        "Congo - Kinshasa" => "Congo - Kinshasa",
        "Cook Islands" => "Cook Islands",
        "Costa Rica" => "Costa Rica",
        "Croatia" => "Croatia",
        "Cuba" => "Cuba",
        "Cyprus" => "Cyprus",
        "Czech Republic" => "Czech Republic",
        "Côte d’Ivoire" => "Côte d’Ivoire",
        "Denmark" => "Denmark",
        "Djibouti" => "Djibouti",
        "Dominica" => "Dominica",
        "Dominican Republic" => "Dominican Republic",
        "Dronning Maud Land" => "Dronning Maud Land",
        "East Germany" => "East Germany",
        "Ecuador" => "Ecuador",
        "Egypt" => "Egypt",
        "El Salvador" => "El Salvador",
        "Equatorial Guinea" => "Equatorial Guinea",
        "Eritrea" => "Eritrea",
        "Estonia" => "Estonia",
        "Ethiopia" => "Ethiopia",
        "Falkland Islands" => "Falkland Islands",
        "Faroe Islands" => "Faroe Islands",
        "Fiji" => "Fiji",
        "Finland" => "Finland",
        "France" => "France",
        "French Guiana" => "French Guiana",
        "French Polynesia" => "French Polynesia",
        "French Southern Territories" => "French Southern Territories",
        "French Southern and Antarctic Territories" => "French Southern and Antarctic Territories",
        "Gabon" => "Gabon",
        "Gambia" => "Gambia",
        "Georgia" => "Georgia",
        "Germany" => "Germany",
        "Ghana" => "Ghana",
        "Gibraltar" => "Gibraltar",
        "Greece" => "Greece",
        "Greenland" => "Greenland",
        "Grenada" => "Grenada",
        "Guadeloupe" => "Guadeloupe",
        "Guam" => "Guam",
        "Guatemala" => "Guatemala",
        "Guernsey" => "Guernsey",
        "Guinea" => "Guinea",
        "Guinea-Bissau" => "Guinea-Bissau",
        "Guyana" => "Guyana",
        "Haiti" => "Haiti",
        "Heard Island and McDonald Islands" => "Heard Island and McDonald Islands",
        "Honduras" => "Honduras",
        "Hong Kong SAR China" => "Hong Kong SAR China",
        "Hungary" => "Hungary",
        "Iceland" => "Iceland",
        "India" => "India",
        "Indonesia" => "Indonesia",
        "Iran" => "Iran",
        "Iraq" => "Iraq",
        "Ireland" => "Ireland",
        "Isle of Man" => "Isle of Man",
        "Israel" => "Israel",
        "Italy" => "Italy",
        "JamaicaJM" => "Jamaica",
        "Japan" => "Japan",
        "Jersey" => "Jersey",
        "Johnston Island" => "Johnston Island",
        "Jordan" => "Jordan",
        "Kazakhstan" => "Kazakhstan",
        "Kenya" => "Kenya",
        "Kiribati" => "Kiribati",
        "Kuwait" => "Kuwait",
        "Kyrgyzstan" => "Kyrgyzstan",
        "Laos" => "Laos",
        "Latvia" => "Latvia",
        "Lebanon" => "Lebanon",
        "Lesotho" => "Lesotho",
        "Liberia" => "Liberia",
        "Libya" => "Libya",
        "Liechtenstein" => "Liechtenstein",
        "Lithuania" => "Lithuania",
        "Luxembourg" => "Luxembourg",
        "Macau SAR China" => "Macau SAR China",
        "Macedonia" => "Macedonia",
        "Madagascar" => "Madagascar",
        "Malawi" => "Malawi",
        "Malaysia" => "Malaysia",
        "Maldives" => "Maldives",
        "Mali" => "Mali",
        "Malta" => "Malta",
        "Marshall Islands" => "Marshall Islands",
        "Martinique" => "Martinique",
        "Mauritania" => "Mauritania",
        "Mauritius" => "Mauritius",
        "Mayotte" => "Mayotte",
        "Metropolitan France" => "Metropolitan France",
        "Mexico" => "Mexico",
        "Micronesia" => "Micronesia",
        "Midway Islands" => "Midway Islands",
        "Moldova" => "Moldova",
        "Monaco" => "Monaco",
        "Mongolia" => "Mongolia",
        "Montenegro" => "Montenegro",
        "Montserrat" => "Montserrat",
        "Morocco" => "Morocco",
        "Mozambique" => "Mozambique",
        "Myanmar [Burma]" => "Myanmar [Burma]",
        "Namibia" => "Namibia",
        "Nauru" => "Nauru",
        "Nepal" => "Nepal",
        "Netherlands" => "Netherlands",
        "Netherlands Antilles" => "Netherlands Antilles",
        "Neutral Zone" => "Neutral Zone",
        "New Caledonia" => "New Caledonia",
        "New Zealand" => "New Zealand",
        "Nicaragua" => "Nicaragua",
        "Niger" => "Niger",
        "Nigeria" => "Nigeria",
        "Niue" => "Niue",
        "Norfolk Island" => "Norfolk Island",
        "North Korea" => "North Korea",
        "North Vietnam" => "North Vietnam",
        "Northern Mariana Islands" => "Northern Mariana Islands",
        "Norway" => "Norway",
        "Oman" => "Oman",
        "Pacific Islands Trust Territory" => "Pacific Islands Trust Territory",
        "Pakistan" => "Pakistan",
        "Palau" => "Palau",
        "Palestinian Territories" => "Palestinian Territories",
        "Panama" => "Panama",
        "Panama Canal Zone" => "Panama Canal Zone",
        "Papua New Guinea" => "Papua New Guinea",
        "Paraguay" => "Paraguay",
        "People's Democratic Republic of Yemen" => "People's Democratic Republic of Yemen",
        "Peru" => "Peru",
        "Philippines" => "Philippines",
        "Pitcairn Islands" => "Pitcairn Islands",
        "Poland" => "Poland",
        "Portugal" => "Portugal",
        "Puerto Rico" => "Puerto Rico",
        "Qatar" => "Qatar",
        "Romania" => "Romania",
        "Russia" => "Russia",
        "Rwanda" => "Rwanda",
        "Réunion" => "Réunion",
        "Saint Barthélemy" => "Saint Barthélemy",
        "Saint Helena" => "Saint Helena",
        "Saint Kitts and Nevis" => "Saint Kitts and Nevis",
        "Saint Lucia" => "Saint Lucia",
        "Saint Martin" => "Saint Martin",
        "Saint Pierre and Miquelon" => "Saint Pierre and Miquelon",
        "Saint Vincent and the Grenadines" => "Saint Vincent and the Grenadines",
        "Samoa" => "Samoa",
        "San Marino" => "San Marino",
        "Saudi Arabia" => "Saudi Arabia",
        "Senegal" => "Senegal",
        "Serbia" => "Serbia",
        "Serbia and Montenegro" => "Serbia and Montenegro",
        "Seychelles" => "Seychelles",
        "Sierra Leone" => "Sierra Leone",
        "Singapore" => "Singapore",
        "Slovakia" => "Slovakia",
        "Slovenia" => "Slovenia",
        "Solomon Islands" => "Solomon Islands",
        "Somalia" => "Somalia",
        "South Africa" => "South Africa",
        "South Georgia and the South Sandwich Islands" => "South Georgia and the South Sandwich Islands",
        "South Korea" => "South Korea",
        "Spain" => "Spain",
        "Sri Lanka" => "Sri Lanka",
        "Sudan" => "Sudan",
        "Suriname" => "Suriname",
        "Svalbard and Jan Mayen" => "Svalbard and Jan Mayen",
        "Swaziland" => "Swaziland",
        "Sweden" => "Sweden",
        "Switzerland" => "Switzerland",
        "Syria" => "Syria",
        "São Tomé and Príncipe" => "São Tomé and Príncipe",
        "Taiwan" => "Taiwan",
        "Tajikistan" => "Tajikistan",
        "Tanzania" => "Tanzania",
        "Thailand" => "Thailand",
        "Timor-Leste" => "Timor-Leste",
        "Togo" => "Togo",
        "Tokelau" => "Tokelau",
        "Tonga" => "Tonga",
        "Trinidad and Tobago" => "Trinidad and Tobago",
        "Tunisia" => "Tunisia",
        "Turkey" => "Turkey",
        "Turkmenistan" => "Turkmenistan",
        "Turks and Caicos Islands" => "Turks and Caicos Islands",
        "Tuvalu" => "Tuvalu",
        "U.S. Minor Outlying Islands" => "U.S. Minor Outlying Islands",
        "U.S. Miscellaneous Pacific Islands" => "U.S. Miscellaneous Pacific Islands",
        "U.S. Virgin Islands" => "U.S. Virgin Islands",
        "Uganda" => "Uganda",
        "Ukraine" => "Ukraine",
        "Union of Soviet Socialist Republics" => "Union of Soviet Socialist Republics",
        "United Arab Emirates" => "United Arab Emirates",
        "United Kingdom" => "United Kingdom",
        "United States" => "United States",
        "Unknown or Invalid Region" => "Unknown or Invalid Region",
        "Uruguay" => "Uruguay",
        "Uzbekistan" => "Uzbekistan",
        "Vanuatu" => "Vanuatu",
        "Vatican City" => "Vatican City",
        "Venezuela" => "Venezuela",
        "Vietnam" => "Vietnam",
        "Wake Island" => "Wake Island",
        "Wallis and Futuna" => "Wallis and Futuna",
        "Western Sahara" => "Western Sahara",
        "Yemen" => "Yemen",
        "Zambia" => "Zambia",
        "Zimbabwe" => "Zimbabwe",
        "Åland Islands" => "Åland Islands",
    ];
}

function getCurrency(): array
{
    return [
        'AFN' => 'Afghanistan Afghani',
        'USD' => 'United States Dollar',
        'EUR' => 'Euro Member Countries',
        'AED' => 'United Arab Emirates Dirham',
        'ALL' => 'Albania Lek',
        'ARS' => 'Argentina Peso',
        'AWG' => 'Aruba Guilder',
        'AUD' => 'Australia Dollar',
        'AZN' => 'Azerbaijan New Manat',
        'BSD' => 'Bahamas Dollar',
        'BBD' => 'Barbados Dollar',
        'BDT' => 'Bangladeshi taka',
        'BYR' => 'Belarus Ruble',
        'BZD' => 'Belize Dollar',
        'BMD' => 'Bermuda Dollar',
        'BOB' => 'Bolivia Boliviano',
        'BAM' => 'Bosnia and Herzegovina Convertible Marka',
        'BWP' => 'Botswana Pula',
        'BGN' => 'Bulgaria Lev',
        'BRL' => 'Brazil Real',
        'BND' => 'Brunei Darussalam Dollar',
        'KHR' => 'Cambodia Riel',
        'CAD' => 'Canada Dollar',
        'KYD' => 'Cayman Islands Dollar',
        'CLP' => 'Chile Peso',
        'CNY' => 'China Yuan Renminbi',
        'COP' => 'Colombia Peso',
        'CRC' => 'Costa Rica Colon',
        'HRK' => 'Croatia Kuna',
        'CUP' => 'Cuba Peso',
        'CZK' => 'Czech Republic Koruna',
        'DKK' => 'Denmark Krone',
        'DOP' => 'Dominican Republic Peso',
        'XCD' => 'East Caribbean Dollar',
        'EGP' => 'Egypt Pound',
        'SVC' => 'El Salvador Colon',
        'EEK' => 'Estonia Kroon',
        'FKP' => 'Falkland Islands (Malvinas) Pound',
        'FJD' => 'Fiji Dollar',
        'GHC' => 'Ghana Cedis',
        'GIP' => 'Gibraltar Pound',
        'GTQ' => 'Guatemala Quetzal',
        'GGP' => 'Guernsey Pound',
        'GYD' => 'Guyana Dollar',
        'HNL' => 'Honduras Lempira',
        'HKD' => 'Hong Kong Dollar',
        'HUF' => 'Hungary Forint',
        'ISK' => 'Iceland Krona',
        'INR' => 'India Rupee',
        'IDR' => 'Indonesia Rupiah',
        'IRR' => 'Iran Rial',
        'IMP' => 'Isle of Man Pound',
        'JMD' => 'Jamaica Dollar',
        'JPY' => 'Japan Yen',
        'JEP' => 'Jersey Pound',
        'KZT' => 'Kazakhstan Tenge',
        'KPW' => 'Korea (North) Won',
        'KRW' => 'Korea (South) Won',
        'KGS' => 'Kyrgyzstan Som',
        'LAK' => 'Laos Kip',
        'LVL' => 'Latvia Lat',
        'LBP' => 'Lebanon Pound',
        'LRD' => 'Liberia Dollar',
        'LTL' => 'Lithuania Litas',
        'MKD' => 'Macedonia Denar',
        'MYR' => 'Malaysia Ringgit',
        'MUR' => 'Mauritius Rupee',
        'MXN' => 'Mexico Peso',
        'MNT' => 'Mongolia Tughrik',
        'MZN' => 'Mozambique Metical',
        'NAD' => 'Namibia Dollar',
        'NPR' => 'Nepal Rupee',
        'ANG' => 'Netherlands Antilles Guilder',
        'NZD' => 'New Zealand Dollar',
        'NIO' => 'Nicaragua Cordoba',
        'NGN' => 'Nigeria Naira',
        'NOK' => 'Norway Krone',
        'OMR' => 'Oman Rial',
        'PKR' => 'Pakistan Rupee',
        'PAB' => 'Panama Balboa',
        'PYG' => 'Paraguay Guarani',
        'PEN' => 'Peru Nuevo Sol',
        'PHP' => 'Philippines Peso',
        'PLN' => 'Poland Zloty',
        'QAR' => 'Qatar Riyal',
        'RON' => 'Romania New Leu',
        'RUB' => 'Russia Ruble',
        'SHP' => 'Saint Helena Pound',
        'SAR' => 'Saudi Arabia Riyal',
        'RSD' => 'Serbia Dinar',
        'SCR' => 'Seychelles Rupee',
        'SGD' => 'Singapore Dollar',
        'SBD' => 'Solomon Islands Dollar',
        'SOS' => 'Somalia Shilling',
        'ZAR' => 'South Africa Rand',
        'LKR' => 'Sri Lanka Rupee',
        'SEK' => 'Sweden Krona',
        'CHF' => 'Switzerland Franc',
        'SRD' => 'Suriname Dollar',
        'SYP' => 'Syria Pound',
        'TWD' => 'Taiwan New Dollar',
        'THB' => 'Thailand Baht',
        'TTD' => 'Trinidad and Tobago Dollar',
        'TRY' => 'Turkey Lira',
        'TRL' => 'Turkey Lira',
        'TVD' => 'Tuvalu Dollar',
        'UAH' => 'Ukraine Hryvna',
        'GBP' => 'United Kingdom Pound',
        'UYU' => 'Uruguay Peso',
        'UZS' => 'Uzbekistan Som',
        'VEF' => 'Venezuela Bolivar',
        'VND' => 'Viet Nam Dong',
        'YER' => 'Yemen Rial',
        'ZWD' => 'Zimbabwe Dollar'
    ];
}

function getModelFilter(): \Filament\Tables\Enums\FiltersLayout
{
    return \Filament\Tables\Enums\FiltersLayout::AboveContentCollapsible;
}

function getDaysBetweenDates($start_date, $end_date, $target_days)
{
    // تبدیل روزهای ورودی به اندیس‌های مربوط به هفته
    $days_indices = [];
    if ($target_days === null) {
        $target_days = [];
    }

    foreach ($target_days as $day) {
        $days_indices[] = strtolower($day) === "sunday" ? 0 :
            (strtolower($day) === "monday" ? 1 :
                (strtolower($day) === "tuesday" ? 2 :
                    (strtolower($day) === "wednesday" ? 3 :
                        (strtolower($day) === "thursday" ? 4 :
                            (strtolower($day) === "friday" ? 5 :
                                (strtolower($day) === "saturday" ? 6 : null))))));
    }

    $selected_dates = []; // آرایه‌ای برای نگهداری تاریخ‌های مورد نظر

    $start = new DateTime($start_date);
    $end = new DateTime($end_date);

    $end->modify('+1 day'); // شامل کردن تاریخ پایان

    $interval = new DateInterval('P1D'); // یک روز اضافه کن
    $date_range = new DatePeriod($start, $interval, $end);

    foreach ($date_range as $date) {
        $day_of_week = $date->format('w'); // شماره روز هفته (0 برای یکشنبه تا 6 برای شنبه)

        if (in_array($day_of_week, $days_indices)) { // بررسی اینکه روز در لیست روزهای هدف است یا نه
            $selected_dates[] = $date->format('Y-m-d');
        }
    }

    return $selected_dates;
}

function getCompanyUrl()
{

    $url = \Illuminate\Support\Facades\Request::url();

    $path = parse_url($url, PHP_URL_PATH);
    $parts = explode('/', $path);
    $index = array_search('admin', $parts);
    if ($index !== false && isset($parts[$index + 1])) {
        \Illuminate\Support\Facades\Cache::set('CompanyId',$parts[$index + 1]);

        return $parts[$index + 1];
    } else {
        if (in_array('livewire', $parts) && in_array('update', $parts)) {

            return getCompany() ? getCompany()->id : \Illuminate\Support\Facades\Cache::get('CompanyId');
        }
        if (getCompany()){
            return  getCompany()->id;
        }
    }
}


function generateNextCode($code): string
{

    $parts = explode('.', $code);
    if (isset($parts[1])) {
        // گرفتن آخرین بخش و تبدیل آن به عدد
        $lastNumber = (int)end($parts);

        // افزایش عدد
        $nextNumber = $lastNumber + 1;

        // جایگزینی آخرین بخش با عدد جدید
        $parts[count($parts) - 1] = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // ترکیب بخش‌ها به یک کد جدید
        return implode('.', $parts);
    }
    $lastNumber = $code;
    $lastNumber = (int)$lastNumber;
    $nextNumber = $lastNumber + 1;

    return str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
}

function defaultCurrency()
{
    return getCompany()->currencies->where('is_company_currency', 1)->first();
}

function PDFdefaultCurrency($company)
{
    return $company->currencies->where('is_company_currency', 1)->first()?->symbol;
}

function generateNextCodePO($code): string
{


    $lastNumber = $code;
    $lastNumber = (int)$lastNumber;
    $nextNumber = $lastNumber + 1;

    return str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
}

function generateNextCodeAsset($code): string
{

    $lastNumber = $code;
    $lastNumber = (int)$lastNumber;
    $nextNumber = $lastNumber + 1;

    return str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
}

function generateNextCodeProduct($code): string
{
    if (preg_match('/^([A-Za-z]+)(\d+)$/', $code, $matches)) {

        $prefix = $matches[1]; // پیشوند (مثلاً IT یا هر چیز دیگر)
        $number = $matches[2]; // عدد (مثلاً 0001 یا 123)

        // افزایش عدد
        $nextNumber = intval($number) + 1;

        // حفظ طول عدد با اضافه کردن صفرهای پیش‌رو
        $nextNumberFormatted = str_pad($nextNumber, strlen($number), '0', STR_PAD_LEFT);

        // ترکیب پیشوند و عدد جدید
        return $prefix . $nextNumberFormatted;
    } else {
        return '0001';
    }
}


function generateNextCodeDote($code): string
{
    // تقسیم کد به بخش‌های مختلف بر اساس نقطه
    $parts = explode('.', $code);

    // گرفتن آخرین بخش و تبدیل آن به عدد
    $lastNumber = (int)end($parts);

    // افزایش عدد
    $nextNumber = $lastNumber + 1;

    // جایگزینی آخرین بخش با عدد جدید
    $parts[count($parts) - 1] = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

    // ترکیب بخش‌ها به یک کد جدید
    return implode('.', $parts);
}

function getPeriod()
{
    return FinancialPeriod::query()->where('status', 'During')->where('company_id', getCompany()->id)->first();
}

function getDocumentCode(): int
{
    return getCompany()->financialPeriods()->where('status', "During")?->first()?->invoices()?->get()->last()?->number != null ? getCompany()->financialPeriods()->where('status', "During")->first()->invoices()->get()->last()->number + 1 : 1;
}

function getGender($record)
{
    if ($record->gender === "male") {
        return "Male";
    } elseif ($record->gender === "female") {
        return "Female";
    } else {
        return "Other";
    }
}

function addSpacesBasedOnParentLevel($record, $level = 0, $visited = [])
{
    $spaces = str_repeat(' ', $level * 10);

    if ($record->parent) {

        return addSpacesBasedOnParentLevel($record->parent, $level + 1);
    }
    return $spaces;
}

function getEmployee()
{
    return auth()->user()->employee;
}


function getParents($record, $visited = [])
{
    $str = "/" . $record?->title;

    // بررسی اینکه آیا والد موجود است و آیا قبلاً بازدید نشده
    if ($record?->parent && !in_array($record->parent->id, $visited)) {
        $visited[] = $record->id;

        $str .= getParents($record->parent, $visited);
    }

    return $str;
}

function sendAR($employee, $record, $company)
{

    if ($employee?->department?->employee_id) {
        if ($employee->department->employee_id === $employee->id) {
            $record->approvals()->create([
                'employee_id' => $employee->department?->employee_id,
                'company_id' => $company->id,
                'position' => 'Head Department',
                'status' => "Approve",
                'approve_date' => now()
            ]);
            $CEO = Employee::query()->firstWhere('user_id', $company->user_id);
            $record->approvals()->create([
                'employee_id' => $CEO->id,
                'company_id' => $company->id,
                'position' => 'CEO',
                'status' => "Pending"
            ]);
        } else {
            $record->approvals()->create([
                'employee_id' => $employee->department->employee_id,
                'company_id' => $company->id,
                'position' => 'Head Department'
            ]);
        }
    } else {
        $CEO = Employee::query()->firstWhere('user_id', $company->user_id);
        $record->approvals()->create([
            'employee_id' => $CEO->id,
            'company_id' => $company->user_id,
            'position' => 'CEO'
        ]);
    }
}

function getAdmin()
{
    $roles = getCompany()->roles->where('name', 'Admin')->first();
    if (isset($roles->users[0])) {
        if ($roles->users[0]->employee) {
            return $roles->users[0]->employee;
        }
    }
}

function getSecurity()
{
    $roles = getCompany()->roles->where('name', 'Security')->first();
    if (isset($roles->users[0])) {
        if ($roles->users[0]->employee) {
            return $roles->users[0]->employee;
        }
    }
}

function getOperation()
{
    $roles = getCompany()->roles->where('name', 'Operation')->first();
    if (isset($roles->users[0])) {
        if ($roles->users[0]->employee) {
            return $roles->users[0]->employee;
        }
    }
}

function sendOperation($employee, $record, $company)
{
    if (getOperation()) {
        if (getOperation()->id === $employee->id) {
            $record->approvals()->create([
                'employee_id' => getAdmin()->id,
                'company_id' => $company->id,
                'position' => 'Operation',
                'status' => "Approve",
                'approve_date' => now()
            ]);
        } else {
            $record->approvals()->create([
                'employee_id' => getOperation()->id,
                'company_id' => $company->id,
                'position' => 'Operation',
            ]);
        }
    }
}

function sendAdmin($employee, $record, $company)
{
    if (getAdmin()) {
        if (getAdmin()->id === $employee->id) {
            $record->approvals()->create([
                'employee_id' => getAdmin()->id,
                'company_id' => $company->id,
                'position' => 'Admin',
                'status' => "Approve",
                'approve_date' => now()
            ]);
            sendSecurity($employee, $record, $company);

        } else {
            $record->approvals()->create([
                'employee_id' => getAdmin()->id,
                'company_id' => $company->id,
                'position' => 'Admin',
            ]);
        }
    }
}

function sendSecurity($employee, $record, $company)
{
    $security = Employee::query()->firstWhere('id', getSecurity()?->id);
    if ($security) {
        if ($security->id === $employee->id) {
            $record->approvals()->create([
                'employee_id' => $security->id,
                'company_id' => $company->id,
                'position' => 'Security',
                'status' => "Approve",
                'approve_date' => now()
            ]);

            if (substr($record->approvals[0]?->approvable_type, 11) === "TakeOut") {
                $record->update(['mood' => 'Approved']);
            } else {
                $record->update(['status' => 'Approved']);

            }
        } else {
            $record->approvals()->create([
                'employee_id' => $security->id,
                'company_id' => $company->id,
                'position' => 'Security',
            ]);
        }
    }

}

function getSelectCurrency()
{
    return Select::make('currency_id')->live()->label('Currency')->default(defaultCurrency()?->id)->required()->relationship('currency', 'name', modifyQueryUsing: fn($query) => $query->where('company_id', getCompany()->id))->searchable()->preload()->createOptionForm([
        \Filament\Forms\Components\Section::make([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('symbol')->required()->maxLength(255),
            TextInput::make('exchange_rate')->required()->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
        ])->columns(3)
    ])->createOptionUsing(function ($data) {
        $data['company_id'] = getCompany()->id;
        Notification::make('success')->title('success')->success()->send();
        return Currency::query()->create($data)->getKey();
    })->editOptionForm([
        \Filament\Forms\Components\Section::make([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('symbol')->required()->maxLength(255),
            TextInput::make('exchange_rate')->required()->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
        ])->columns(3)
    ]);
}


function getAllPermission(): array
{
    return [
        1, 2, 3, 4, 5, 9, 10, 11, 13, 14, 15, 16, 17, 21, 22, 23, 37, 38, 39, 40, 41, 45, 46, 47, 49, 50,
        51, 52, 53, 57, 58, 59, 61, 62, 63, 64, 65, 69, 70, 71, 73, 74, 75, 76, 77, 81, 82, 83, 85, 86,
        87, 88, 89, 93, 94, 95, 97, 98, 99, 100, 101, 105, 106, 107, 109, 110, 111, 112, 113, 117, 118,
        119, 121, 122, 123, 124, 125, 129, 130, 131, 133, 134, 135, 136, 137, 141, 142, 143, 145, 146,
        147, 148, 149, 153, 154, 155, 157, 158, 159, 160, 161, 165, 166, 167, 169, 170, 171, 172, 173,
        177, 178, 179, 181, 182, 183, 184, 189, 190, 193, 194, 195, 196, 197, 201, 202, 203, 205, 206,
        207, 208, 209, 213, 214, 215, 217, 218, 219, 220, 221, 225, 226, 227, 229, 230, 231, 232, 233,
        237, 238, 239, 241, 242, 243, 244, 245, 249, 250, 251, 253, 254, 255, 256, 257, 261, 262, 263,
        265, 266, 267, 268, 269, 273, 274, 275, 277, 278, 279, 280, 281, 285, 286, 287, 289, 290, 291,
        292, 293, 297, 298, 299, 301, 302, 303, 304, 305, 309, 310, 311, 313, 314, 315, 316, 317, 321,
        322, 323, 325, 326, 327, 328, 329, 333, 334, 335, 337, 338, 339, 340, 341, 345, 346, 347, 349,
        350, 351, 352, 353, 357, 358, 359, 361, 362, 363, 364, 365, 369, 370, 371, 373, 374, 375, 376,
        377, 381, 382, 383, 385, 386, 387, 388, 389, 390, 391, 392, 393, 394, 395, 399, 400, 401, 403,
        404, 405, 406, 407, 411, 412, 413, 415, 416, 417, 418, 423, 424, 427, 428, 429, 430, 431, 435,
        436, 437, 439, 440, 441, 442, 443, 447, 448, 449, 451, 452, 453, 454, 455, 459, 460, 461, 463,
        464, 465, 466, 471, 472, 475, 476, 477, 478, 479, 483, 484, 485, 487, 509, 510, 511, 512, 513,
        514, 518, 519, 520, 521, 522, 523, 525, 527, 528, 529, 530, 531, 535, 536, 537, 539, 540, 541,
        542, 543, 544, 545, 546, 547, 548, 549, 550, 551, 552, 553, 554, 557
    ];
}
