<?php

return [
    'breadcrumb' => [
        'title' => 'Targeted Advertisements Edit',
        'home' => 'Home',
        'advertisements' => 'Targeted Advertisements',
        'page' => 'Edit',
    ],
    'content' => [
        'title' => 'Targeted Advertisements Edit',
        'inputs' => [
            'type' => 'Platform',
            'type_values' => [
                'any' => 'Any Platform',
                'website' => 'Website',
                'mobile' => 'Mobile',
            ],
            'users' => 'Users',
            'users_values' => [
                'any' => 'Any Users',
                'advertisers' => 'Advertisers',
                'customers' => 'Customers',
            ],
            'name' => 'Advertiser Name',
            'url' => 'Advertiser URL',
            'advertiser_image' => 'Advertiser Image',
            'content' => 'Content',
            'files' => 'Media',
            'files_note' => 'Please note that if you upload files in here it will replace the old uploaded files.',
            'categories' => 'Categories',
            'selected_categories' => 'Selected Categories',
            'countries' => 'Countries',
            'selected_countries' => 'Selected Countries',
            'selected_cites' => 'Selected Cites',
            'starts_at' => 'Starts At',
            'ends_at' => 'Ends At',
            'is_active' => 'Active',
            'boolean' => [
                'yes' => 'Yes',
                'no' => 'No',
            ],
            'include' => 'Include',
            'exclude' => 'Exclude',
            'placeholders' => [
                'name' => "name",
                'search' => "Search",
            ]
        ],
        'submit' => 'Save',
        'callbacks' => [
            'success' => 'Advertisement has been edited successfully!',
            'error' => 'Something went wrong, Please try again later!.'
        ]
    ],
];
