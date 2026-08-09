<?php

return [
    'breadcrumb' => [
        'title' => 'Add Currency',
        'home' => 'Home',
        'currencies' => 'Currencies',
        'page' => 'Add',
    ],
    'content' => [
        'title' => 'Add Currency',
        'inputs' => [
            'code' => 'Code',
            'code_placeholder' => 'e.g. EGP',
            'symbol' => 'Symbol',
            'name_en' => 'Name (English)',
            'name_ar' => 'Name (Arabic)',
            'exchange_rate' => 'Exchange Rate (vs. base currency)',
            'exchange_rate_notes' => 'The value of one unit of the base currency in this currency.',
            'is_active' => 'Active (usable on packages)',
            'is_visible' => 'Visible In App',
            'is_visible_notes' => 'Controls whether this currency appears in the app\'s currency switcher.',
            'boolean' => [
                'yes' => 'Yes',
                'no' => 'No',
            ],
        ],
        'submit' => 'Add',
    ],
];
