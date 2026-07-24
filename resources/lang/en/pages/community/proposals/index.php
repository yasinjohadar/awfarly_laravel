<?php

return [
    'breadcrumb' => [
        'title' => 'Proposals Inquiry',
        'home' => 'Home',
        'community' => 'Community',
        'proposals' => 'Proposals',
        'page' => 'Inquiry',
    ],
    'content' => [
        'title' => 'Proposals Inquiry',
        'tabs' => [
            'all' => 'All Proposals (:count)',
            'unanswered' => 'Unanswered Proposals (:count)',
            'answered' => 'Answered Proposals (:count)',
        ]
    ],
    'datatable' => [
        'advertiser_id' => 'Advertiser #',
        'advertiser_name' => 'Advertiser Name',
        'user_id' => 'User #',
        'user_type' => 'User Type',
        'users_types' => [
            'customer' => 'Customer',
            'advertiser' => 'Advertiser',
        ],
        'user_name' => 'User Name',
        'content' => 'Content',
        'expires_at' => 'Expires At',
        'expires_in' => 'Expires In',
        'answered_at' => 'Answered At',
        'status' => 'Status',
    ],
    'modal' => [
        'edit' => [
            'title' => 'Edit Proposals',
            'inputs' => [
                'content' => 'Content',
                'answer' => 'Answer',
            ],
            'submit' => 'Save changes',
            'cancel' => 'Cancel',
        ],
        'delete' => [
            'title' => 'Delete Proposals',
            'select-option' => 'Select delete type: ',
            'soft-delete' => 'Soft Delete',
            'permanent-delete' => 'Permanently Delete',
            'content' => 'Are you sure you want to delete these Proposals?',
            'submit' => 'Delete',
            'cancel' => 'Cancel',
        ],
        'restore' => [
            'title' => 'Restore Proposals',
            'content' => 'Are you sure you want to restore this proposal?',
            'submit' => 'Restore',
            'cancel' => 'Cancel',
        ]
    ]
];
