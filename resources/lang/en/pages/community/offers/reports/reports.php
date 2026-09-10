<?php

return [
    'breadcrumb' => [
        'title' => 'Reports Inquiry',
        'home' => 'Home',
        'community' => 'Community',
        'offers' => 'Offers',
        'reports' => 'Reports',
        'page' => 'Inquiry',
    ],
    'content' => [
        'title' => 'Reported Offers',
        'datatable' => [
            'offer_id' => 'Offer #',
            'offer_content' => 'Offer Content',
            'reports_count' => 'Reports Count',
            'latest_reason' => 'Latest Report',
            'created_at' => 'Created At',
            'action_view' => 'View offer & report details',
            'action_delete_post' => 'Delete the offer itself',
            'action_delete_reports' => 'Delete reports only (keeps the offer)',
        ],
    ],
    'modal' => [
        'delete' => [
            'title' => 'Delete Reports',
            'content' => 'Are you sure you want to delete these reports? The offers themselves will not be deleted.',
            'submit' => 'Delete Reports',
            'cancel' => 'Cancel',
        ],
        'delete_post' => [
            'title' => 'Delete Offer',
            'content' => 'Are you sure you want to permanently delete this offer? Its reports will be marked as solved — the reports themselves will not be deleted.',
            'submit' => 'Delete Offer',
            'cancel' => 'Cancel',
        ],
    ]
];
