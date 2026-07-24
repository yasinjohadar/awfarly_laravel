<?php

return [
    'content' => [
        'title' => 'Payment #<strong>:id</strong> Inquiry',
        'back' => 'Back',
        'package_id' => 'Package #',
        'package_name' => 'Package Name',
        'advertiser_id' => 'Advertiser #',
        'advertiser_name' => 'Advertiser Name',
        'starts_at' => 'Starts At',
        'ends_at' => 'Ends At',
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
            'title' => 'Delete Payment item',
            'content' => 'Are you sure you want to delete this Payment item?',
            'submit' => 'Delete',
            'cancel' => 'Cancel',
        ],
    ]
];
