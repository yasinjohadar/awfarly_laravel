<?php

return [
    'breadcrumb' => [
        'title' => 'Governorates Inquiry',
        'home' => 'Home',
        'countries' => 'Countries',
        'governorates' => 'Governorates',
        'page' => 'Inquiry',
    ],
    'content' => [
        'title' => 'Country <strong>:name</strong> Governorates',
        'title_all' => 'All Governorates',
        'add' => 'Add Governorate',
        'datatable' => [
            'country' => 'Country',
            'country_code' => 'Country Code',
            'name_en' => 'EN Name',
            'name_ar' => 'AR Name',
        ],
    ],
    'modal' => [
        'edit' => [
            'title' => 'Edit Governorate',
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
            'title' => 'Delete Governorate',
            'content' => 'Are you sure you want to delete the governorate: :name?',
            'title_multiple' => 'Delete Governorates',
            'content_multiple' => 'Are you sure you want to delete these governorates?',
            'submit' => 'Delete',
            'cancel' => 'Cancel',
            'in_use' => 'This governorate cannot be deleted because it has cities or is linked to users or posts.',
        ],
    ],
];
