<?php

return [
    'breadcrumb' => [
        'title' => 'عرض الدول',
        'home' => 'الصفحة الرئيسية',
        'countries' => 'الدول',
        'page' => 'عرض',
    ],
    'content' => [
        'title' => 'عرض الدول',
        'datatable' => [
            'code' => 'رمز الدولة',
            'name_en' => 'الإسم باللغة الإنجليزية',
            'name_ar' => 'الإسم باللغة العربية',
            'mobile_code' => 'رمز الهاتف',
            'cities_count' => 'المدن',
            'active' => 'نشط',
        ],
    ],
    'modal' => [
        'edit' => [
            'title' => 'تعديل الدول',
            'inputs' => [
                'code' => 'رمز الدولة',
                'name_en' => 'الإسم باللغة الإنجليزية',
                'name_ar' => 'الإسم باللغة العربية',
                'mobile_code' => 'رمز الهاتف',
                'is_active' => 'نشط',
                'boolean' => [
                    'yes' => 'نعم',
                    'no' => 'لا',
                ],
            ],
            'submit' => 'حفظ التعديلات',
            'cancel' => 'إلغاء',
        ],
        'delete' => [
            'title' => 'حذف الدول',
            'content' => 'هل أنت متأكد أنك تريد حذف هذه الدول؟',
            'submit' => 'حذف',
            'cancel' => 'إلغاء',
        ],
    ]
];
