<?php

return [
    'breadcrumb' => [
        'title' => 'Promotions Inquiry',
        'home' => 'Home',
        'categories' => 'Promotions',
        'page' => 'Inquiry',
    ],
    'content' => [
        'title' => 'Category <strong>:Title</strong> Sub Promotions',
        'datatable' => [
            'title_en' => 'EN Title',
            'title_ar' => 'AR Title',
            'body_ar' => 'AR Description',
            'body_en' => 'EN Description',
            'start_at' => 'Start Date',
            'end_at' => 'End Date',
            'link' => 'Optional Link',
            'image' => 'Image',
            'sub_categories_count' => 'Sub Promotions',
            'active' => 'Active',
        ],
        'back' => 'Back',
        'add' => 'Add',
        'sort' => 'Sort',
    ],
    'modal' => [
        'edit' => [
            'title' => 'Edit Promotions',
            'inputs' => [
                'parent' => 'Parent Category',
                'title_en' => 'English Title',
                'title_ar' => 'Arabic Title',
                'body_ar' => 'AR Description',
                'body_en' => 'EN Description',
                'image' => 'Image',
                'link' => 'Optional Link',
                'start_at' => 'Start Date',
                'end_at' => 'End Date',
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
            'title' => 'Add Sub Promotions',
            'inputs' => [
                'title_en' => 'English Title',
                'title_ar' => 'Arabic Title',
                'body_ar' => 'AR Description',
                'body_en' => 'EN Description',
                'image' => 'Image',
                'placeholders' => [
                    'choose_file' => 'Image',
                ]
            ],
            'submit' => 'Save changes',
            'cancel' => 'Cancel',
        ],
        'delete' => [
            'title' => 'Delete Promotions',
            'content' => 'Are you sure you want to delete these categories?',
            'submit' => 'Delete',
            'cancel' => 'Cancel',
        ],
    ]
];
