<?php

return [
    'breadcrumb' => [
        'title' => 'Username Change Inquiry',
        'home' => 'Home',
        'requests' => 'Requests',
        'contact-us' => 'Username Change',
        'page' => 'Inquiry',
    ],
    'content' => [
        'title' => 'Username Change Inquiry',
        'all' => 'All (:count)',
        'approved' => 'Approved (:count)',
        'pending' => 'Pending (:count)',
        'declined' => 'Declined (:count)',
        'datatable' => [
            'user_type' => 'User Type',
            'user_id' => 'User #',
            'user_name' => 'User Name',
            'old_username' => 'Old Username',
            'new_username' => 'New Username',
            'reason' => 'Reason',
            'status' => 'Status',
            'created_at' => 'Created At',
            'approved' => 'Approved',
            'declined' => 'Declined',
            'pending' => 'Pending',
            'types' => [
                'customer' => 'Customer',
                'advertiser' => 'Advertiser',
            ],
        ],
    ],
    'modal' => [
        'approved' => [
            'title' => 'Approve Changing Username',
            'content' => 'Are you sure you want to approve changing this username?',
            'submit' => 'Confirm',
            'cancel' => 'Cancel',
        ],
        'declined' => [
            'title' => 'Decline Changing Username',
            'content' => 'Are you sure you want to decline changing this username?',
            'submit' => 'Confirm',
            'cancel' => 'Cancel',
        ],

    ]
];
