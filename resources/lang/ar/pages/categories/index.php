<?php

return [
    'breadcrumb' => [
        'title' => 'عرض الأقسام',
        'home' => 'الصفحة الرئيسية',
        'categories' => 'الأقسام',
        'page' => 'عرض',
    ],
    'content' => [
        'title' => 'الأقسام الفرعية للقسم <strong>:name</strong>',
        'datatable' => [
            'name_en' => 'الإسم باللغة الإنجليزية',
            'name_ar' => 'الإسم باللغة العربية',
            'description' => 'الوصف',
            'image' => 'الصورة',
            'sub_categories_count' => 'الأقسام الفرعية',
            'active' => 'نشط',
        ],
        'back' => 'رجوع',
        'add' => 'إضافة',
        'sort' => 'تعديل الترتيب',
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
                'is_active' => 'نشط',
                'boolean' => [
                    'yes' => 'نعم',
                    'no' => 'لا',
                ],
                'placeholders' => [
                    'choose_file' => 'الصورة',
                ]
            ],
            'submit' => 'حفظ التعديلات',
            'cancel' => 'إلغاء',
        ],
        'add' => [
            'title' => 'إضافة أقسام فرعية',
            'inputs' => [
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
            'title' => 'حذف القسم',
            'content' => 'هل أنت متأكد أنك تريد حذف القسم: :name؟',
            'title_multiple' => 'حذف الأقسام',
            'content_multiple' => 'هل أنت متأكد أنك تريد حذف هذه الأقسام؟',
            'submit' => 'حذف',
            'cancel' => 'إلغاء',
            'in_use' => 'لا يمكن حذف هذا القسم لأنه يحتوي على أقسام فرعية.',
        ],
    ]
];
