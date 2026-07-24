<?php

return [
    'breadcrumb' => [
        'title' => 'Side Advertisements Create',
        'home' => 'Home',
        'advertisements' => 'Advertisements',
        'side' => 'Side',
        'page' => 'Create',
    ],
    'content' => [
        'title' => 'Side Advertisements Create',
        'inputs' => [
            'url' => 'Advertisement URL',
            'image' => 'Image',
            'side' => [
                'title' => 'Side',
                'left' => 'Left',
                'right' => 'Right',
            ],
            'starts_at' => 'Starts At',
            'ends_at' => 'Ends At',
            'is_expired' => 'Expired',
            'boolean' => [
                'yes' => 'Yes',
                'no' => 'No',
            ],
        ],
        'submit' => 'Create',
        'callbacks' => [
            'success' => 'Advertisement has been created successfully!',
            'error' => 'Something went wrong, Please try again later!.'
        ]
    ],
];
