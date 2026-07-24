<?php

return [
    'datatable' => [
        'user_id' => 'Advertiser #',
        'user_name' => 'Advertiser Name',
        'content' => 'Content',
        'sale_percentage' => 'Sale Percentage',
        'advertisement_url' => 'Advertisement URL',
        'expires_at' => 'Expires At',
        'expires_in' => 'Expires In (days)',
        'deleted_at' => 'Deleted At',
        'status' => 'Status',
        'rate' => 'Rate',
        'likes_count' => 'Likes',
        'views_count' => 'Views',
        'comments_count' => 'Comments',
    ],
    'modal' => [
        'edit' => [
            'title' => 'Edit Offers',
            'inputs' => [
                'content' => 'Content',
                'sale_percentage' => 'Sale Percentage',
                'advertisement_url' => 'Advertisement URL',
                'expires_in' => 'Expires In (days)',
                'expires_at' => 'Expires At',
                'status' => 'Status',
                'rate' => 'Rate',
                'pending' => 'Pending',
                'approved' => 'Approved',
                'views_count' => 'Views Count',
                'likes_count' => 'Likes Count',
                'comments_count' => 'Comments Count',
            ],
            'submit' => 'Save changes',
            'cancel' => 'Cancel',
        ],
        'delete' => [
            'title' => 'Delete Offers',
            'select-option' => 'Select delete type: ',
            'soft-delete' => 'Soft Delete',
            'permanent-delete' => 'Permanently Delete',
            'content' => 'Are you sure you want to delete these offers?',
            'submit' => 'Delete',
            'cancel' => 'Cancel',
        ],
        'restore' => [
            'title' => 'Restore Offer',
            'content' => 'Are you sure you want to restore this offer?',
            'submit' => 'Restore',
            'cancel' => 'Cancel',
        ]
    ]
];
