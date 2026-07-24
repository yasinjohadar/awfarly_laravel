<?php

return [
    'breadcrumb' => [
        'title' => 'Admins Actions Logs',
        'home' => 'Home',
        'system' => 'System',
        'page' => 'Admins Actions Logs',
    ],
    'content' => [
        'title' => 'Admins Actions Logs',
        'datatable' => [
            'summary' => 'Summary',
            'admin_id' => 'Admin ID',
            'admin_name' => 'Admin Name',
            'type' => 'Type',
            'log_action' => 'Log Action',
            'date' => 'Date',
            'time' => 'Time',
        ],
    ],
    'modal' => [
        'delete' => [
            'title' => 'Delete Admins Actions Logs',
            'content' => 'Are you sure you want to delete these logs?',
            'submit' => 'Delete',
            'cancel' => 'Cancel',
        ],
        'show' => [
            'title' => 'Show Admins Actions Logs',
            'close' => 'Close',
            'content' => [
                'log_id' => 'Log ID: ',
                'summary' => 'Log Summary: ',
                'type' => 'Log Type: ',
                'action' => 'Log Action: ',
                'admin_id' => 'Admin ID: ',
                'admin_name' => 'Admin Name: ',
                'data' => 'Data: ',
                'no_data' => 'There are no data to show!',
            ],
        ],
    ]
];
