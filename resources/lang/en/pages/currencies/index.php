<?php

return [
    'breadcrumb' => [
        'title' => 'View Currencies',
        'home' => 'Home',
        'currencies' => 'Currencies',
        'page' => 'View',
    ],
    'content' => [
        'title' => 'View Currencies',
        'add' => 'Add Currency',
        'datatable' => [
            'code' => 'Code',
            'name_en' => 'Name (English)',
            'name_ar' => 'Name (Arabic)',
            'symbol' => 'Symbol',
            'exchange_rate' => 'Exchange Rate',
            'is_base' => 'Base Currency',
            'active' => 'Active',
            'visible' => 'Visible In App',
        ],
    ],
    'modal' => [
        'edit' => [
            'title' => 'Edit Currency',
            'inputs' => [
                'code' => 'Code',
                'name_en' => 'Name (English)',
                'name_ar' => 'Name (Arabic)',
                'symbol' => 'Symbol',
                'exchange_rate' => 'Exchange Rate (vs. base currency)',
                'base_rate_locked' => 'The base currency\'s exchange rate is always fixed at 1.',
                'is_base' => 'Set As Base Currency',
                'is_active' => 'Active',
                'is_visible' => 'Visible In App',
                'boolean' => [
                    'yes' => 'Yes',
                    'no' => 'No',
                ],
            ],
            'submit' => 'Save Changes',
            'cancel' => 'Cancel',
            'base_must_stay_active' => 'The base currency cannot be deactivated or hidden.',
        ],
        'delete' => [
            'title' => 'Delete Currency',
            'content' => 'Are you sure you want to delete the currency: :name?',
            'title_multiple' => 'Delete Currencies',
            'content_multiple' => 'Are you sure you want to delete these currencies?',
            'submit' => 'Delete',
            'cancel' => 'Cancel',
            'in_use' => 'The base currency, or a currency currently used by a package, cannot be deleted.',
        ],
    ],
];
