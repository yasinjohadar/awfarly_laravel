<?php

return [
    'breadcrumb' => [
        'title' => 'Categories Inquiry',
        'home' => 'Home',
        'categories' => 'Categories',
        'page' => 'Inquiry',
    ],
    'content' => [
        'title' => 'Category <strong>:name</strong> Sub Categories',
        'datatable' => [
            'name_en' => 'EN Name',
            'name_ar' => 'AR Name',
            'description' => 'Description',
            'image' => 'Image',
            'sub_categories_count' => 'Sub Categories',
            'active' => 'Active',
        ],
        'back' => 'Back',
        'add' => 'Add',
        'sort' => 'Sort',
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
                'is_active' => 'Active',
                'boolean' => [
                    'yes' => 'Yes',
                    'no' => 'No',
                ],
                'placeholders' => [
                    'choose_file' => 'Image',
                ]
            ],
            'submit' => 'Save changes',
            'cancel' => 'Cancel',
        ],
        'add' => [
            'title' => 'Add Sub Categories',
            'inputs' => [
                'name_en' => 'English Name',
                'name_ar' => 'Arabic Name',
                'description' => 'Description',
                'image' => 'Image',
                'placeholders' => [
                    'choose_file' => 'Image',
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
