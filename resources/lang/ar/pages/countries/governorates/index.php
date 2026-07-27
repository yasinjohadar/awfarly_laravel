<?php

return [
    'breadcrumb' => [
        'title' => 'عرض المحافظات',
        'home' => 'الصفحة الرئيسية',
        'countries' => 'الدول',
        'governorates' => 'المحافظات',
        'page' => 'عرض',
    ],
    'content' => [
        'title' => 'محافظات الدولة <strong>:name</strong>',
        'title_all' => 'جميع المحافظات',
        'add' => 'إضافة محافظة',
        'datatable' => [
            'country' => 'الدولة',
            'country_code' => 'رمز الدولة',
            'name_en' => 'الإسم باللغة الإنجليزية',
            'name_ar' => 'الإسم باللغة العربية',
        ],
    ],
    'modal' => [
        'edit' => [
            'title' => 'تعديل المحافظة',
            'inputs' => [
                'country' => 'الدولة',
                'country_code' => 'رمز الدولة',
                'name_en' => 'الإسم باللغة الإنجليزية',
                'name_ar' => 'الإسم باللغة العربية',
            ],
            'submit' => 'حفظ التعديلات',
            'cancel' => 'إلغاء',
        ],
        'delete' => [
            'title' => 'حذف المحافظات',
            'content' => 'هل أنت متأكد أنك تريد حذف المحافظات المحددة؟',
            'submit' => 'حذف',
            'cancel' => 'إلغاء',
        ],
    ],
];
