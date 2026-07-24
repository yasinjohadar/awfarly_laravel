<?php

return [
    'breadcrumb' => [
        'title' => 'Subscriptions Payments Create',
        'home' => 'Home',
        'subscriptions' => 'Subscriptions',
        'payments' => 'Payments',
        'page' => 'Create',
    ],
    'content' => [
        'title' => 'Subscriptions Payments Create',
        'inputs' => [
            'name' => 'Name',
            'description' => 'Description',
            'specifications' => 'Specifications',
            'maximum_posts' => 'Posts Count',
            'price' => 'Price',
            'old_price' => 'Old Price',
            'duration' => 'Duration',
            'subscription_type' => 'Subscription Type',
            'subscription_types' => [
                'minutely' => 'Minutely',
                'hourly' => 'Hourly',
                'daily' => 'Daily',
                'weekly' => 'Weekly',
                'monthly' => 'Monthly',
                'yearly' => 'Yearly',
            ],
            'currency' => 'Currency',
            'currencies' => [
                'SAR' => 'SAR',
                'USD' => 'USD',
                'EGP' => 'EGP',
                'KWD' => 'KWD',
                'AED' => 'AED',
            ],
            'is_visible' => 'Visible',
            'is_active' => 'Active',
            'boolean' => [
                'yes' => 'Yes',
                'no' => 'No',
            ]
        ],
        'submit' => 'Create',
    ],
];
