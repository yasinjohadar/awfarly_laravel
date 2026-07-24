<?php

return [
    'breadcrumb' => [
        'title' => 'Reported Offers',
        'home' => 'Home',
        'community' => 'Community',
        'offers' => 'Offers',
        'page' => 'Reports',
    ],
    'content' => [
        'title' => 'Reported Offers',
        'datatable' => [
            'offer_id' => 'Offer #',
            'user_type' => 'User Type',
            'user_id' => 'User #',
            'user_name' => 'User Name',
            'type' => 'Type',
            'types' => [
                'Sexually Inappropriate' => 'Sexually Inappropriate',
                'Abusive Content' => 'Abusive Content',
                'Misleading or Scam' => 'Misleading or Scam',
                'Offensive' => 'Offensive',
                'Violence' => 'Violence',
                'Prohibited Content' => 'Prohibited Content',
                'Spam' => 'Spam',
                'False News' => 'False News',
                'Other' => 'Other',
            ],
            'reason' => 'Reason',
            'created_at' => 'Created At',
            'reports_count' => 'Reports Count',
            'guest' => 'Guest',
        ],
    ],
    'modal' => [
        'show' => [
            'offer_id' => 'Offer #: ',
            'user_type' => 'User Type: ',
            'user_id' => 'User #: ',
            'user_name' => 'User Name: ',
            'reason' => 'Reason: ',
            'created_at' => 'Created At: ',
        ],
        'delete' => [
            'title' => 'Delete Reported Offers',
            'content' => 'Are you sure you want to delete these reports?',
            'submit' => 'Delete',
            'cancel' => 'Cancel',
        ],
    ]
];
