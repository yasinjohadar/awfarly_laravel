<?php

return [
    'breadcrumb' => [
        'title' => 'Interests Inquiry',
        'home' => 'Home',
        'interests' => 'Interests',
        'page' => 'Inquiry',
    ],
    'content' => [
        'title' => 'Interest <strong>:name</strong> Sub Interests',
        'datatable' => [
            'name_en' => 'EN Name',
            'name_ar' => 'AR Name',
            'description' => 'Description',
            'image' => 'Image',
            'sub_interests_count' => 'Sub Interests',
            'active' => 'Active',
        ],
        'back' => 'Back',
        'add' => 'Add',
        'sort' => 'Sort',
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
            'title' => 'Add Sub Interests',
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
            'title' => 'Delete Interest',
            'content' => 'Are you sure you want to delete the interest: :name?',
            'title_multiple' => 'Delete Interests',
            'content_multiple' => 'Are you sure you want to delete these interests?',
            'submit' => 'Delete',
            'cancel' => 'Cancel',
            'in_use' => 'This interest cannot be deleted because it has sub interests.',
        ],
    ]
];
