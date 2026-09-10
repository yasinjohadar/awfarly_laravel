<?php

return [
    'breadcrumb' => [
        'title' => 'Reports Inquiry',
        'home' => 'Home',
        'community' => 'Community',
        'offers' => 'Offers',
        'comments' => 'Comments',
        'reports' => 'Reports',
        'page' => 'Inquiry',
    ],
    'content' => [
        'title' => 'Reported Offers Comments',
        'datatable' => [
            'comment_id' => 'Comment #',
            'comment_content' => 'Comment Text',
            'reports_count' => 'Reports Count',
            'latest_reason' => 'Latest Report',
            'created_at' => 'Created At',
            'action_view' => 'View comment & report details',
            'action_delete_post' => 'Delete the comment itself',
            'action_delete_reports' => 'Delete reports only (keeps the comment)',
        ],
    ],
    'modal' => [
        'delete' => [
            'title' => 'Delete Reported Comments',
            'content' => 'Are you sure you want to delete these reports? The comments themselves will not be deleted.',
            'submit' => 'Delete Reports',
            'cancel' => 'Cancel',
        ],
        'delete_post' => [
            'title' => 'Delete Comment',
            'content' => 'Are you sure you want to permanently delete this comment? Its reports will be marked as solved — the reports themselves will not be deleted.',
            'submit' => 'Delete Comment',
            'cancel' => 'Cancel',
        ],
    ]
];
