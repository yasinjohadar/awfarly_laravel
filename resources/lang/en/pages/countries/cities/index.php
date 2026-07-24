<?php

return [
    'breadcrumb' => [
        'title' => 'Cities Inquiry',
        'home' => 'Home',
        'countries' => 'Countries',
        'cities' => 'Cities',
        'page' => 'Inquiry',
    ],
    'content' => [
        'title' => 'Country <strong>:name</strong> Cities',
        'datatable' => [
            'country' => 'Country',
            'country_code' => 'Country Code',
            'name_en' => 'EN Name',
            'name_ar' => 'AR Name',
        ],
    ],
    'modal' => [
        'edit' => [
            'title' => 'Edit Cities',
            'inputs' => [
                'country' => 'Country',
                'country_code' => 'Country Code',
                'name_en' => 'EN Name',
                'name_ar' => 'AR Name',
            ],
            'submit' => 'Save changes',
            'cancel' => 'Cancel',
        ],
        'delete' => [
            'title' => 'Delete Cities',
            'content' => 'Are you sure you want to delete these cities?',
            'submit' => 'Delete',
            'cancel' => 'Cancel',
        ],
    ]
];
