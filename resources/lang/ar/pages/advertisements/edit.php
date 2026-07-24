<?php

return [
    'breadcrumb' => [
        'title' => 'تعديل الإعلانات موجهة',
        'home' => 'الصفحة الرئيسية',
        'advertisements' => 'الإعلانات الموجهة',
        'page' => 'تعديل',
    ],
    'content' => [
        'title' => 'تعديل الإعلانات موجهة',
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
            'files_note' => 'برجاء العلم أنك إذا أضفت ملفات هنا سيتم إستبدالها بالملفات القديمة المضافة بهذا الإعلان!',
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
        'submit' => 'حفظ',
        'callbacks' => [
            'success' => 'تم تعديل الإعلان بنجاح',
            'error' => 'حدث خطأ ما، برجاء المحاوله لاحقاً!.'
        ]
    ],
];
