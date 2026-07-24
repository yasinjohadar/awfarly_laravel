<?php

return [
    'breadcrumb' => [
        'title' => 'Admins Inquiry',
        'home' => 'Home',
        'admins' => 'Admins',
        'page' => 'Inquiry',
    ],
    'content' => [
        'title' => 'Admins Inquiry',
        'datatable' => [
            'name' => 'Name',
            'image' => 'Image',
            'email' => 'Email',
            'mobile' => 'Mobile',
            'username' => 'Username',
            'language' => 'Language',
            'email_verified' => 'Email Verified',
            'mobile_verified' => 'Mobile Verified',
            'last_login_at' => 'Last Login At',
            'status' => 'Status',
            'status_type' => [
                'active' => 'Active',
                'banned' => 'Banned',
                'closed' => 'Closed',
            ],
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ],
    ],
    'modal' => [
        'edit' => [
            'title' => 'Edit Admins Users',
            'inputs' => [
                'name' => 'Name',
                'email' => 'Email',
                'roles' => 'Roles',
                'mobile' => 'Mobile',
                'username' => 'Username',
                'image' => 'Image',
                'language' => 'Language',
                'password' => 'Password',
                'status_options' => [
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                    'banned' => 'Banned',
                ],
                'placeholders' => [
                    'name' => "Admin's Name",
                    'email' => "Admin's Email",
                    'mobile' => "Admin's Mobile",
                    'username' => "Admin's Username",
                    'language' => 'Select Language',
                    'password' => "Leaving this field empty means the password won't be changed!",
                ],
                'status' => 'Status',
            ],
            'submit' => 'Save changes',
            'cancel' => 'Cancel',
        ],
        'delete' => [
            'title' => 'Delete Admins Users',
            'content' => 'Are you sure you want to delete these users?',
            'submit' => 'Delete',
            'cancel' => 'Cancel',
        ],
    ]
];
