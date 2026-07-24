<?php

return [
    'content' => [
        'title' => 'عرض المدفوعات <strong>:id</strong>',
        'back' => 'رجوع',
        'package_id' => 'خططة #',
        'package_name' => 'إسم الخططة',
        'advertiser_id' => 'معلن #',
        'advertiser_name' => 'إسم المعلن',
        'starts_at' => 'تبدء في',
        'ends_at' => 'تنتهي في',
        'is_ended' => 'منتهية',
        'is_current' => 'الحالية',
        'is_active' => 'نشط',
        'deleted_at' => 'تم حذفة في',
        'boolean' => [
            'yes' => 'نعم',
            'no' => 'لا',
        ],
    ],
    'modal' => [
        'edit' => [
            'title' => 'تعديل المدفوعات',
            'inputs' => [
                'starts_at' => 'تبدء في',
                'ends_at' => 'تنتهي في',
                'is_ended' => 'منتهية',
                'is_current' => 'الحالية',
                'is_active' => 'نشط',
                'boolean' => [
                    'yes' => 'نعم',
                    'no' => 'لا',
                ]
            ],
            'submit' => 'حفظ التعديلات',
            'cancel' => 'إلغاء',
        ],
        'delete' => [
            'title' => 'حذف المدفوعات',
            'content' => 'هل أنت متأكد أنك تريد حذف المدفوعات؟',
            'submit' => 'حذف',
            'cancel' => 'إلغاء',
        ],
    ]
];
