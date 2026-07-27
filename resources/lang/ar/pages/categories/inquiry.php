<?php

return [
    'breadcrumb' => [
        'title' => 'عرض الأقسام',
        'home' => 'الصفحة الرئيسية',
        'categories' => 'الأقسام',
        'page' => 'عرض',
    ],
    'content' => [
        'title' => 'عرض الأقسام',
        'add' => 'إضافة قسم',
        'datatable' => [
            'parent' => 'القسم الرئيسي #',
            'name_en' => 'الإسم باللغة الإنجليزية',
            'name_ar' => 'الإسم باللغة العربية',
            'description' => 'الوصف',
            'image' => 'الصورة',
        ],
    ],
    'modal' => [
        'edit' => [
            'title' => 'تعديل الأقسام',
            'inputs' => [
                'parent' => 'القسم الرئيسي',
                'name_en' => 'الإسم باللغة الإنجليزية',
                'name_ar' => 'الإسم باللغة العربية',
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
            'title' => 'حذف الأقسام',
            'content' => 'هل أنت متأكد أنك تريد حذف هذه الأقسام؟',
            'submit' => 'حذف',
            'cancel' => 'إلغاء',
        ],
    ]
];
