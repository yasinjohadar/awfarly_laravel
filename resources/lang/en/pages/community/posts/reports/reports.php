<?php

return [
    'breadcrumb' => [
        'title' => 'Reports Inquiry',
        'home' => 'Home',
        'community' => 'Community',
        'posts' => 'Posts',
        'reports' => 'Reports',
        'page' => 'Inquiry',
    ],
    'content' => [
        'title' => 'Reported Posts',
        'datatable' => [
            'post_id' => 'Post #',
            'post_content' => 'Post Content',
            'reports_count' => 'Reports Count',
            'latest_reason' => 'Latest Report',
            'created_at' => 'Created At',
            'action_view' => 'View post & report details',
            'action_delete_post' => 'Delete the post itself',
            'action_delete_reports' => 'Delete reports only (keeps the post)',
        ],
    ],
    'modal' => [
        'delete' => [
            'title' => 'Delete Reports',
            'content' => 'Are you sure you want to delete these reports? The posts themselves will not be deleted.',
            'content_single' => 'Are you sure you want to delete the reports for this post? The post itself will not be deleted.',
            'submit' => 'Delete Reports',
            'cancel' => 'Cancel',
        ],
        'delete_post' => [
            'title' => 'Delete Post',
            'content' => 'Are you sure you want to permanently delete this post? Its reports will be marked as solved — the reports themselves will not be deleted.',
            'submit' => 'Delete Post',
            'cancel' => 'Cancel',
        ],
    ]
];
