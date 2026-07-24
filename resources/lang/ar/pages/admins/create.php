<?php

return [
    'breadcrumb' => [
        'title' => 'إضافة مدراء',
        'home' => 'الصفحة الرئيسية',
        'admins' => 'المدراء',
        'page' => 'إضافة',
    ],
    'content' => [
        'title' => 'إضافة مدراء',
        'inputs' => [
            'name' => 'الإسم',
            'roles' => 'الصلاحيات',
            'email' => 'البريد الإلكتروني',
            'mobile' => 'رقم الهاتف',
            'username' => 'إسم المستحدم',
            'language' => 'اللغة',
            'password' => 'الرقم السري',
            'image' => 'الصورة',
            'placeholders' => [
                'name' => "إسم المدير",
                'email' => "بريد المدير الإلكتروني",
                'mobile' => "رقم هاتف المدير",
                'username' => "إسم المعرف للمدير",
                'password' => "رقم المدير السري",
                'choose_file' => "الصورة الشخصية",
                'language' => 'إختر لغة',
                'browse' => 'Browse',
            ],
            'status' => 'الحالة',
            'status_options' => [
                'active' => 'مفعل',
                'inactive' => 'معطل',
                'banned' => 'مطرود',
            ],
        ],
        'submit' => 'إضافة',
    ],
];
