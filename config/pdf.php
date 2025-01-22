<?php

return [
    'mode' => 'utf-8',
    'format' => '',
    'author' => '',
    'subject' => '',
    'keywords' => '',
    'creator' => 'Laravel Pdf',
    'display_mode' => 'fullpage',
    'tempDir' => base_path('storage/temp'),
    'font_path' => base_path('storage/fonts/'),
    'font_data' => [
        'ttt' => [
            'R' => 'IRANSansWeb_Bold.ttf', // regular font
            'B' => 'IRANSansWeb_Bold.ttf', // optional: bold font
            'I' => 'IRANSansWeb_Bold.ttf', // optional: italic font
            'BI' => 'IRANSansWeb_Bold.ttf', // optional: bold-italic font
            'useOTL' => 0xFF, // required for complicated langs like Persian, Arabic and Chinese
            'useKashida' => 75, // required for complicated langs like Persian, Arabic and Chinese
        ] // ...add as many as you want.
    ]
];
