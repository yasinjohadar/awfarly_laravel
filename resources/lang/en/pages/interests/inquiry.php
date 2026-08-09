<?php

return [
    'breadcrumb' => [
        'title' => 'Interests Inquiry',
        'home' => 'Home',
        'interests' => 'Interests',
        'page' => 'Inquiry',
    ],
    'content' => [
        'title' => 'Interests Inquiry',
        'add' => 'Add Interest',
        'datatable' => [
            'parent' => 'Parent Interest ID',
            'name_en' => 'EN Name',
            'name_ar' => 'AR Name',
            'description' => 'Description',
            'image' => 'Image',
        ],
    ],
    'modal' => [
        'edit' => [
            'title' => 'Edit Interests',
            'inputs' => [
                'parent' => 'Parent Interest',
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
            'title' => 'Delete Interests',
            'content' => 'Are you sure you want to delete these interests?',
            'submit' => 'Delete',
            'cancel' => 'Cancel',
        ],
    ]
];
