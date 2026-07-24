<?php

return [
    'breadcrumb' => [
        'title' => 'Advertisers Ratings Inquiry',
        'home' => 'Home',
        'advertisers' => 'Advertisers',
        'ratings' => 'Ratings',
        'page' => 'Inquiry',
    ],
    'content' => [
        'title' => 'Advertisers Ratings Inquiry',
        'datatable' => [
            'advertiser_id' => 'Advertiser #',
            'advertiser_name' => 'Advertiser Name',
            'user_type' => 'User Type',
            'user_types' => [
                'customer' => 'Customer',
                'advertiser' => 'Advertiser',
            ],
            'user_id' => 'User #',
            'user_name' => 'User Name',
            'comment' => 'Comment',
            'rate' => 'Rate',
            'status' => 'Status',
            'status_types' => [
                'approved' => 'Approved',
                'pending' => 'Pending',
                'unapproved' => 'Declined',
            ],
            'created_at' => 'Created At',
        ],
        'tabs' => [
            'all' => 'All (:count)',
            'approved' => 'Approved (:count)',
            'pending' => 'Pending (:count)',
            'unapproved' => 'Unapproved (:count)',
        ]
    ],
    'modal' => [
        'edit' => [
            'title' => 'Edit Rating',
            'inputs' => [
                'status' => 'status',
                'approved' => 'Approved',
                'pending' => 'Pending',
                'unapproved' => 'Unapproved',
                'comment' => 'Comment',
                'rate' => 'Rate',
            ],
            'submit' => 'Save changes',
            'cancel' => 'Cancel',
        ],
        'delete' => [
            'title' => 'Delete Advertisers Users',
            'content' => 'Are you sure you want to delete these users?',
            'submit' => 'Delete',
            'cancel' => 'Cancel',
        ],
    ]
];
