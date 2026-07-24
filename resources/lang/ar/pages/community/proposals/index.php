<?php

return [
    'breadcrumb' => [
        'title' => 'عرض طلبات التسعير',
        'home' => 'الصفحة الرئيسية',
        'community' => 'المجتمع',
        'proposals' => 'طلبات التسعير',
        'page' => 'عرض',
    ],
    'content' => [
        'title' => 'عرض طلبات التسعير',
        'tabs' => [
            'all' => 'كل طلبات التسعير (:count)',
            'unanswered' => 'طلبات التسعير الغير مردود عليها (:count)',
            'answered' => 'طلبات التسعير المردود عليها (:count)',
        ]
    ],
    'datatable' => [
        'advertiser_id' => 'معلن #',
        'advertiser_name' => 'إسم المعلن',
        'user_type' => 'نوع المستخدم',
        'users_types' => [
            'customer' => 'مستخدم',
            'advertiser' => 'معلن',
        ],
        'user_id' => 'المستخدم #',
        'user_name' => 'إسم المعرف',
        'content' => 'المضمون',
        'expires_at' => 'ينتهي في',
        'expires_in' => 'ينتهي خلال',
        'answered_at' => 'تم الإجابة في',
        'status' => 'الحالة',
    ],
    'modal' => [
        'edit' => [
            'title' => 'تعديل طلبات التسعير',
            'inputs' => [
                'content' => 'المضمون',
                'answer' => 'الإجابة',
            ],
            'submit' => 'حفظ التعديلات',
            'cancel' => 'إلغاء',
        ],
        'delete' => [
            'title' => 'حذف طلبات التسعير',
            'select-option' => 'إختر نوع الحذف: ',
            'soft-delete' => 'حذف جزئي',
            'permanent-delete' => 'حذف نهائي',
            'content' => 'هل أنت متأكد أنك تريد حذف طلبات التسعير هذه؟',
            'submit' => 'حذف',
            'cancel' => 'إلغاء',
        ],
        'restore' => [
            'title' => 'إستعادة طلبات التسعير',
            'content' => 'هل أنت متأكد أنك تريد إستعادة طلب التسعير هذا؟',
            'submit' => 'إستعادة',
            'cancel' => 'إلغاء',
        ]
    ]
];
