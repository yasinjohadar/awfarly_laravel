<?php

return [
    'breadcrumb' => [
        'title' => 'Posts Inquiry',
        'home' => 'Home',
        'community' => 'Community',
        'posts' => 'Posts',
        'page' => 'Inquiry',
    ],
    'content' => [
        'title' => 'Posts Inquiry',
        'tabs' => [
            'all' => 'All Posts (:count)',
            'active' => 'Active Posts (:count)',
            'deleted' => 'Deleted Posts (:count)',
        ]
    ],
    'datatable' => [
        'user_type' => 'User Type',
        'user_id' => 'User #',
        'user_name' => 'User Name',
        'governorate' => 'Governorate',
        'city' => 'City',
        'content' => 'Content',
        'views_count' => 'Views',
        'likes_count' => 'Likes',
        'comments_count' => 'Comments',
        'shares_count' => 'Shares',
    ],
    'modal' => [
        'edit' => [
            'title' => 'Edit Posts',
            'inputs' => [
                'governorate' => 'Governorate',
                'city' => 'City',
                'select_governorate' => 'Select governorate',
                'select_city' => 'Select city',
                'views_count' => 'Views Count',
                'likes_count' => 'Likes Count',
                'comments_count' => 'Comments Count',
            ],
            'submit' => 'Save changes',
            'cancel' => 'Cancel',
        ],
        'delete' => [
            'title' => 'Delete Posts',
            'select-option' => 'Select delete type: ',
            'soft-delete' => 'Soft Delete',
            'permanent-delete' => 'Permanently Delete',
            'content' => 'Are you sure you want to delete these posts?',
            'submit' => 'Delete',
            'cancel' => 'Cancel',
        ],
        'restore' => [
            'title' => 'Restore Post',
            'content' => 'Are you sure you want to restore this post?',
            'submit' => 'Restore',
            'cancel' => 'Cancel',
        ],
    ],
];
