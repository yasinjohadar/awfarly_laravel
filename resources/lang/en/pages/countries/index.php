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
        'add' => 'Add Country',
        'datatable' => [
            'code' => 'Country Code',
            'name_en' => 'EN Name',
            'name_ar' => 'AR Name',
            'mobile_code' => 'Mobile Code',
            'cities_count' => 'Governorates',
            'governorates_count' => 'Governorates',
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
            'title' => 'Delete Country',
            'content' => 'Are you sure you want to delete the country: :name?',
            'title_multiple' => 'Delete Countries',
            'content_multiple' => 'Are you sure you want to delete these countries?',
            'submit' => 'Delete',
            'cancel' => 'Cancel',
            'in_use' => 'This country cannot be deleted because it has governorates or is linked to users.',
        ],
    ]
];
