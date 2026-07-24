<?php

return [
    'breadcrumb' => [
        'title' => 'العروض الترويجية',
        'home' => 'الصفحة الرئيسية',
        'categories' => 'العرض',
        'page' => 'عرض',
    ],
    'content' => [
        'title' => 'العروض الترويجية',
        'datatable' => [
            'parent' => 'القسم الرئيسي #',
            'title_en' => 'الإسم باللغة الإنجليزية',
            'title_ar' => 'الإسم باللغة العربية',
            'description' => 'الوصف',
            'image' => 'الصورة',
        ],
    ],
    'modal' => [
        'edit' => [
            'title' => 'تعديل العرض',
            'inputs' => [
                'parent' => 'القسم الرئيسي',
                'title_en' => 'الإسم باللغة الإنجليزية',
                'title_ar' => 'الإسم باللغة العربية',
                'description' => 'الوصف',
                'image' => 'الصورة',
                'placeholders' => [
                    'choose_file' => 'صورة',
                ]
            ],
            'submit' => 'حفظ التعديلات',
            'cancel' => 'إلغاء',
        ],
        'delete' => [
            'title' => 'حذف العرض',
            'content' => 'هل أنت متأكد أنك تريد حذف هذه العرض؟',
            'submit' => 'حذف',
            'cancel' => 'إلغاء',
        ],
    ]
];
