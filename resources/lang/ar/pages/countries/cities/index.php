<?php

return [
    'breadcrumb' => [
        'title' => 'عرض المدن',
        'home' => 'الصفحة الرئيسية',
        'countries' => 'الدول',
        'cities' => 'المدن',
        'page' => 'عرض',
    ],
    'content' => [
        'title' => 'مدن المحافظة <strong>:name</strong>',
        'title_all' => 'جميع المدن',
        'add' => 'إضافة مدينة',
        'datatable' => [
            'governorate' => 'المحافظة',
            'country' => 'الدولة',
            'country_code' => 'رمز الدولة',
            'name_en' => 'الإسم باللغة الإنجليزية',
            'name_ar' => 'الإسم باللغة العربية',
        ],
    ],
    'modal' => [
        'edit' => [
            'title' => 'تعديل المدينة',
            'inputs' => [
                'governorate' => 'المحافظة',
                'name_en' => 'الإسم باللغة الإنجليزية',
                'name_ar' => 'الإسم باللغة العربية',
            ],
            'submit' => 'حفظ التعديلات',
            'cancel' => 'إلغاء',
        ],
        'delete' => [
            'title' => 'حذف المدن',
            'content' => 'هل أنت متأكد أنك تريد حذف المدن المحددة؟',
            'submit' => 'حذف',
            'cancel' => 'إلغاء',
        ],
    ],
];
