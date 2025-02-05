<?php


use App\Models\Employee;
use App\Models\FinancialPeriod;

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
    $url =  \Illuminate\Support\Facades\Request::url();
    $path = parse_url($url, PHP_URL_PATH);
    $parts = explode('/', $path);
    $index = array_search('admin', $parts);
    if ($index !== false && isset($parts[$index + 1])) {
        return $parts[$index + 1];
    }else{
        return  1;
    }
}


function generateNextCode($code): string
{

    $lastNumber = $code;
    $lastNumber = (int)$lastNumber;
    $nextNumber = $lastNumber + 1;

    return str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
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

function addSpacesBasedOnParentLevel($record, $level = 0,$visited = [])
{
    $spaces = str_repeat(' ', $level * 10);

    if ($record->parent) {

        return addSpacesBasedOnParentLevel($record->parent, $level + 1);
    }
    return $spaces;
}

function getEmployee(){
    return auth()->user()->employee;
}


function getParents($record, $visited = []) {
    $str = "/".$record?->title;

    // بررسی اینکه آیا والد موجود است و آیا قبلاً بازدید نشده
    if ($record?->parent && !in_array($record->parent->id, $visited)) {
        $visited[] = $record->id;

        $str .= getParents($record->parent, $visited);
    }

    return $str;
}

function sendAR($employee, $record,$company)
{

    if ($employee?->department?->employee_id) {
        if ($employee->department->employee_id === $employee->id) {
            $record->approvals()->create([
                'employee_id' => $employee->department?->employee_id,
                'company_id' => $company->id,
                'position' => 'Head Department',
                'status' => "Approve",
                'approve_date'=>now()
            ]);
            $CEO=Employee::query()->firstWhere('user_id',$company->user_id);
            $record->approvals()->create([
                'employee_id'=>$CEO->id,
                'company_id'=>$company->id,
                'position'=>'CEO',
                'status'=>"Pending"
            ]);
        } else {
            $record->approvals()->create([
                'employee_id' => $employee->department->employee_id,
                'company_id' => $company->id,
                'position' => 'Head Department'
            ]);
        }
    }else{
        $CEO=Employee::query()->firstWhere('user_id',$company->user_id);
        $record->approvals()->create([
            'employee_id' => $CEO->id,
            'company_id' => $company->user_id,
            'position' => 'CEO'
        ]);
    }
}
