<?php

return [
    'breadcrumb' => [
        'title' => 'إضافة إعلانات موجهة',
        'home' => 'الصفحة الرئيسية',
        'advertisements' => 'الإعلانات الموجهة',
        'page' => 'إضافة',
    ],
    'content' => [
        'title' => 'إضافة إعلانات موجهة',
        'inputs' => [
            'type' => 'المنصة',
            'type_values' => [
                'any' => 'اي منصة',
                'website' => 'الموقع',
                'mobile' => 'الهاتف',
            ],
            'users' => 'المستخدمين',
            'users_values' => [
                'any' => 'اي مستخدم',
                'advertisers' => 'المعلنين',
                'customers' => 'المستخدمين',
            ],
            'name' => 'إسم المعلن',
            'url' => 'رابط المعلن',
            'advertiser_image' => 'صورة المعلن',
            'content' => 'المضمون',
            'files' => 'الملفات',
            'categories' => 'الأقسام',
            'selected_categories' => 'الأقسام المختارة',
            'countries' => 'الدول',
            'selected_countries' => 'الدول المختارة',
            'selected_cites' => 'المدن المختارة',
            'starts_at' => 'يبدء في',
            'ends_at' => 'ينتهي في',
            'is_active' => 'نشط',
            'boolean' => [
                'yes' => 'نعم',
                'no' => 'لا',
            ],
            'include' => 'أدرج',
            'exclude' => 'إستثني',
            'placeholders' => [
                'name' => "الإسم",
                'search' => "البحث",
            ]
        ],
        'submit' => 'أضف',
        'callbacks' => [
            'success' => 'تم إضافة الإعلان بنجاح!',
            'error' => 'حدث خطأ ما، برجاء المحاوله لاحقاً!.'
        ]
    ],
];
