<?php

return [
    'content' => [
        'title' => 'Contact Form #<strong>:id</strong> Inquiry',
        'back' => 'Back',
        'read' => 'Mark As Read',
        'type' => 'Type',
        'name' => 'Name',
        'mobile' => 'Mobile',
        'whatsapp_mobile' => 'Whatsapp Mobile',
        'email' => 'Email',
        'message' => 'Message',
        'status' => 'Status',
        'created_at' => 'Created At',
        'types' => [
            'Enquiry' => 'Enquiry',
            'Complaint' => 'Complaint',
            'Suggestion' => 'Suggestion',
            'Payments' => 'Payments',
            'Technical support' => 'Technical support',
            'In-app advertising' => 'In-app advertising',
            'Report a problem' => 'Report a problem',
        ],
        'actions' => [
            'mark_read' => 'Mark as read',
            'mark_unread' => 'Mark as unread',
            'delete' => 'Delete request',
            'call' => 'Call',
            'whatsapp' => 'WhatsApp',
            'email' => 'Send email',
        ],
        'status_labels' => [
            'read' => 'Read',
            'unread' => 'Unread',
        ],
        'sections' => [
            'contact_info' => 'Contact details',
            'message' => 'Message',
        ],
    ],
    'modal' => [
        'confirm' => [
            'title' => [
                'read' => 'Read Message',
                'unread' => 'Unread Message',
            ],
            'content' => [
                'read' => 'Are you sure you want to mark this message as read?',
                'unread' => 'Are you sure you want to mark this message as unread?',
            ],
            'submit' => 'Confirm',
            'cancel' => 'Cancel',
        ],
        'delete' => [
            'title' => 'Delete Contact Us Requests',
            'content' => 'Are you sure you want to delete these requests?',
            'content_single' => 'Are you sure you want to delete this request?',
            'submit' => 'Delete',
            'cancel' => 'Cancel',
        ],
    ],
];
