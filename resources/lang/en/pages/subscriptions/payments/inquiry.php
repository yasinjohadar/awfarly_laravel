<?php

return [
    'breadcrumb' => [
        'title' => 'Subscriptions Payments Inquiry',
        'home' => 'Home',
        'subscriptions' => 'Subscriptions',
        'packages' => 'Packages',
        'page' => 'Inquiry',
    ],
    'content' => [
        'title' => 'Subscriptions Payments Inquiry',
    ],
    'datatable' => [
        'package_id' => 'Package #',
        'package_name' => 'Package Name',
        'advertiser_id' => 'Advertiser #',
        'advertiser_name' => 'Advertiser Name',
        'starts_at' => 'Starts At',
        'ends_at' => 'Ends At',
        'purchase_count' => 'Purchase Count',
        'total_price' => 'Total Price',
        'is_ended' => 'Ended',
        'is_current' => 'Current',
        'is_active' => 'Active',
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
            'title' => 'Delete Payments',
            'select-option' => 'Select delete type: ',
            'soft-delete' => 'Soft Delete',
            'permanent-delete' => 'Permanently Delete',
            'content' => 'Are you sure you want to delete these Payments?',
            'submit' => 'Delete',
            'cancel' => 'Cancel',
        ],
        'restore' => [
            'title' => 'Restore Payment',
            'content' => 'Are you sure you want to restore this payment?',
            'submit' => 'Restore',
            'cancel' => 'Cancel',
        ]
    ],
];
