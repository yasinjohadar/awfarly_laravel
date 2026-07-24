<?php

return [
    'content' => [
        'title' => 'Rating #<strong>:id</strong> Inquiry',
        'back' => 'Back',
        'rating_id' => 'Rating #',
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
            'title' => 'Delete Media item',
            'content' => 'Are you sure you want to delete this Media item?',
            'submit' => 'Delete',
            'cancel' => 'Cancel',
        ],
    ]
];
