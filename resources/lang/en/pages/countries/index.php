<?php

return [
    'breadcrumb' => [
        'title' => 'Countries Inquiry',
        'home' => 'Home',
        'countries' => 'Countries',
        'page' => 'Inquiry',
    ],
    'content' => [
        'title' => 'Countries Inquiry',
        'datatable' => [
            'code' => 'Country Code',
            'name_en' => 'EN Name',
            'name_ar' => 'AR Name',
            'mobile_code' => 'Mobile Code',
            'cities_count' => 'Cities',
            'active' => 'Active',
        ],
    ],
    'modal' => [
        'edit' => [
            'title' => 'Edit Countries',
            'inputs' => [
                'code' => 'Country Code',
                'name_en' => 'EN Name',
                'name_ar' => 'AR Name',
                'mobile_code' => 'Mobile Code',
                'is_active' => 'Active',
                'boolean' => [
                    'yes' => 'Yes',
                    'no' => 'No',
                ],
            ],
            'submit' => 'Save changes',
            'cancel' => 'Cancel',
        ],
        'delete' => [
            'title' => 'Delete Countries',
            'content' => 'Are you sure you want to delete these countries?',
            'submit' => 'Delete',
            'cancel' => 'Cancel',
        ],
    ]
];
