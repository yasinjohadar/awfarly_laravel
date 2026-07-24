<?php

return [
    'breadcrumb' => [
        'title' => 'Slider Advertisements Create',
        'home' => 'Home',
        'advertisements' => 'Advertisements',
        'slider' => 'Slider',
        'page' => 'Create',
    ],
    'content' => [
        'title' => 'Slider Advertisements Create',
        'inputs' => [
            'url' => 'Advertisement URL',
            'image' => 'Image',
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
