<?php

return [
    'datatable' => [
        'post_id' => 'Post #',
        'user_type' => 'User Type',
        'user_id' => 'User #',
        'user_name' => 'User Name',
        'comment' => 'Comment',
        'deleted_at' => 'Deleted At',
        'created_at' => 'Created At',
    ],
    'modal' => [
        'edit' => [
            'title' => 'Edit Comments',
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
            'title' => 'Delete Comments',
            'select-option'=>'Select delete type: ',
            'soft-delete'=>'Soft Delete',
            'permanent-delete'=>'Permanently Delete',
            'content' => 'Are you sure you want to delete these Comments?',
            'submit' => 'Delete',
            'cancel' => 'Cancel',
        ],
        'restore' => [
            'title' => 'Restore Comment',
            'content' => 'Are you sure you want to restore this comment?',
            'submit' => 'Restore',
            'cancel' => 'Cancel',
        ]
    ]
];
