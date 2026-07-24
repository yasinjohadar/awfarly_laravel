<?php

return [
    'breadcrumb' => [
        'title' => 'Admins Create',
        'home' => 'Home',
        'admins' => 'Admins',
        'page' => 'Create',
    ],
    'content' => [
        'title' => 'Admins Create',
        'inputs' => [
            'name' => 'Name',
            'roles' => 'Roles',
            'email' => 'Email',
            'mobile' => 'Mobile',
            'username' => 'Username',
            'language' => 'Language',
            'password' => 'Password',
            'image' => 'Image',
            'placeholders' => [
                'name' => "Admin's Name",
                'email' => "Admin's Email",
                'mobile' => "Admin's Mobile",
                'username' => "Admin's Username",
                'password' => "Admin's Password",
                'choose_file' => "Image",
                'language' => 'Select Language',
                'browse' => 'Browse',
            ],
            'status' => 'Status',
            'status_options' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
                'banned' => 'Banned',
            ],
        ],
        'submit' => 'Create',
    ],
];
