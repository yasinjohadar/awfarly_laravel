<?php

return [
    'breadcrumb' => [
        'title' => 'Categories Inquiry',
        'home' => 'Home',
        'categories' => 'Categories',
        'page' => 'Inquiry',
    ],
    'content' => [
        'title' => 'Categories Inquiry',
        'datatable' => [
            'parent' => 'Parent Category ID',
            'name_en' => 'EN Name',
            'name_ar' => 'AR Name',
            'description' => 'Description',
            'image' => 'Image',
        ],
    ],
    'modal' => [
        'edit' => [
            'title' => 'Edit Categories',
            'inputs' => [
                'parent' => 'Parent Category',
                'name_en' => 'English Name',
                'name_ar' => 'Arabic Name',
                'description' => 'Description',
                'image' => 'Image',
                'placeholders' => [
                    'choose_file' => 'Choose Image',
                ]
            ],
            'submit' => 'Save changes',
            'cancel' => 'Cancel',
        ],
        'delete' => [
            'title' => 'Delete Categories',
            'content' => 'Are you sure you want to delete these categories?',
            'submit' => 'Delete',
            'cancel' => 'Cancel',
        ],
    ]
];
