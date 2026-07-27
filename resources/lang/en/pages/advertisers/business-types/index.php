<?php

return [
    'breadcrumb' => [
        'title' => 'Business Types Inquiry',
        'home' => 'Home',
        'advertisers' => 'Advertisers',
        'business_types' => 'Business Types',
        'page' => 'Inquiry',
    ],
    'content' => [
        'title' => 'Business Types Inquiry',
        'add' => 'Add Business Type',
        'datatable' => [
            'name_en' => 'English Name',
            'name_ar' => 'Arabic Name',
            'is_active' => 'Active',
        ],
    ],
    'modal' => [
        'edit' => [
            'title' => 'Edit Business Types',
            'inputs' => [
                'name_en' => 'English Name',
                'name_ar' => 'Arabic Name',
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
            'title' => 'Delete Business Types',
            'content' => 'Are you sure you want to delete these business types?',
            'submit' => 'Delete',
            'cancel' => 'Cancel',
        ],
    ]
];
