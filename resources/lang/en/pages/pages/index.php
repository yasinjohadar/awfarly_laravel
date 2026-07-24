<?php

return [
    'breadcrumb' => [
        'title' => 'Pages Inquiry',
        'home' => 'Home',
        'pages' => 'Pages',
        'page' => 'Inquiry',
    ],
    'content' => [
        'title' => 'Pages Inquiry',
        'datatable' => [
            'slug' => 'Slug',
            'name_en' => 'English Name',
            'name_ar' => 'Arabic Name',
            'title_en' => 'English Title',
            'title_ar' => 'Arabic Title',
            'contents_en' => 'English Contents',
            'contents_ar' => 'Arabic Contents',
            'is_protected' => 'Protected',
            'is_active' => 'Active',
        ],
    ],
    'modal' => [
        'edit' => [
            'title' => 'Edit Pages',
            'inputs' => [
                'slug' => 'Slug',
                'name_en' => 'English Name',
                'name_ar' => 'Arabic Name',
                'title_en' => 'English Title',
                'title_ar' => 'Arabic Title',
                'contents_en' => 'English Contents',
                'contents_ar' => 'Arabic Contents',
                'is_protected' => 'Protected',
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
            'title' => 'Delete Pages',
            'content' => 'Are you sure you want to delete these pages?',
            'submit' => 'Delete',
            'cancel' => 'Cancel',
        ],
    ]
];
