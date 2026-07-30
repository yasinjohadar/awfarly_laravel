<?php

return [
    'content' => [
        'title' => 'Payment #<strong>:id</strong> Inquiry',
        'back' => 'Back',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'payment_id' => 'Payment ID',
        'package_id' => 'Package #',
        'package_name' => 'Package Name',
        'advertiser_id' => 'Advertiser #',
        'advertiser_name' => 'Advertiser Name',
        'starts_at' => 'Starts At',
        'ends_at' => 'Ends At',
        'remaining' => 'Remaining',
        'remaining_days' => ':days days left',
        'ended_ago' => 'Ended :days days ago',
        'no_end_date' => 'No end date',
        'progress' => 'Progress',
        'timeline_title' => 'Subscription timeline',
        'parties_title' => 'Package & advertiser',
        'status_title' => 'Status',
        'purchase_count' => 'Purchase count',
        'is_ended' => 'Ended',
        'is_current' => 'Current',
        'is_active' => 'Active',
        'deleted_at' => 'Deleted At',
        'boolean' => [
            'yes' => 'Yes',
            'no' => 'No',
        ],
    ],
    'modal' => [
        'edit' => [
            'title' => 'Edit Payment',
            'inputs' => [
                'starts_at' => 'Starts At',
                'ends_at' => 'Ends At',
                'is_ended' => 'Ended',
                'is_current' => 'Current',
                'is_active' => 'Active',
                'boolean' => [
                    'yes' => 'Yes',
                    'no' => 'No',
                ]
            ],
            'submit' => 'Save changes',
            'cancel' => 'Cancel',
        ],
        'delete' => [
            'title' => 'Delete Payment',
            'content' => 'Are you sure you want to delete the subscription for plan: :name?',
            'submit' => 'Delete',
            'cancel' => 'Cancel',
        ],
    ]
];
