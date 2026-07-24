<?php

return [
    'breadcrumb' => [
        'title' => 'عرض المدراء',
        'home' => 'الصفحه الرئيسية',
        'admins' => 'المدراء',
        'page' => 'عرض',
    ],
    'content' => [
        'title' => 'عرض المدراء',
        'datatable' => [
            'name' => 'الإسم',
            'image' => 'لصوره الشخصية',
            'email' => 'البريد الإلكتروني',
            'mobile' => 'رقم الهاتف',
            'username' => 'إسم المعرف',
            'language' => 'اللغة',
            'email_verified' => 'بريد إلكتروني مفعل',
            'mobile_verified' => 'هاتف مفعل',
            'last_login_at' => 'أخر دخول في',
            'status' => 'الحالة',
            'status_type' => [
                'active' => 'مفعل',
                'banned' => 'مطرود',
                'closed' => 'مغلق',
            ],
            'created_at' => 'أنشئ في',
            'updated_at' => 'عُدل في',
        ],
    ],
    'modal' => [
        'edit' => [
            'title' => 'تعديل حسابات المدراء',
            'inputs' => [
                'name' => 'الإسم',
                'email' => 'البريد الإلكتروني',
                'roles' => 'الصلاحيات',
                'mobile' => 'رقم الهاتف',
                'username' => 'إسم المعرف',
                'image' => 'الصورة',
                'language' => 'اللغة',
                'password' => 'الرقم السري',
                'placeholders'=> [
                    'name' => "إسم المدير",
                    'email' => "بريد المدير الإلكتروني",
                    'mobile' => "رقم هاتف المدير",
                    'username' => "إسم مستخدم المدير",
                    'language' => 'إختر لغة',
                    'password' => "ترك هذه الخانه فارغه يعني ان الرقم السري لن يتغير!",
                ],
                'status_options' => [
                    'active' => 'مفعل',
                    'inactive' => 'معطل',
                    'banned' => 'مطرود',
                ],
                'status' => 'الحالة',
            ],
            'submit' => 'حفظ التعديلات',
            'cancel' => 'إلغاء',
        ],
        'delete' => [
            'title' => 'حذف حسابات المدراء',
            'content' => 'هل أنت متأكد من حذف هذه الحسابات؟',
            'submit' => 'حذف',
            'cancel' => 'إلغاء',
        ],
    ]
];
