<?php

return [
    'breadcrumb' => [
        'title' => 'عرض الاهتمامات',
        'home' => 'الصفحة الرئيسية',
        'interests' => 'الاهتمامات',
        'page' => 'عرض',
    ],
    'content' => [
        'title' => 'الاهتمامات الفرعية للاهتمام <strong>:name</strong>',
        'datatable' => [
            'name_en' => 'الإسم باللغة الإنجليزية',
            'name_ar' => 'الإسم باللغة العربية',
            'description' => 'الوصف',
            'image' => 'الصورة',
            'sub_interests_count' => 'الاهتمامات الفرعية',
            'active' => 'نشط',
        ],
        'back' => 'رجوع',
        'add' => 'إضافة',
        'sort' => 'تعديل الترتيب',
    ],
    'modal' => [
        'edit' => [
            'title' => 'تعديل الاهتمامات',
            'inputs' => [
                'parent' => 'الاهتمام الرئيسي',
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
            'title' => 'إضافة اهتمامات فرعية',
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
            'title' => 'حذف الاهتمام',
            'content' => 'هل أنت متأكد أنك تريد حذف الاهتمام: :name؟',
            'title_multiple' => 'حذف الاهتمامات',
            'content_multiple' => 'هل أنت متأكد أنك تريد حذف هذه الاهتمامات؟',
            'submit' => 'حذف',
            'cancel' => 'إلغاء',
            'in_use' => 'لا يمكن حذف هذا الاهتمام لأنه يحتوي على اهتمامات فرعية.',
        ],
    ]
];
