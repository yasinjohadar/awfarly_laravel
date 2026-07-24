<?php

return [
    'breadcrumb' => [
        'title' => 'عرض مدفوعات الإشتراكات',
        'home' => 'الصفحة الرئيسية',
        'subscriptions' => 'الإشتراكات',
        'packages' => 'الخطط',
        'page' => 'عرض',
    ],
    'content' => [
        'title' => 'عرض مدفوعات الإشتراكات',
    ],
    'datatable' => [
        'package_id' => 'خططة #',
        'package_name' => 'إسم الخططة',
        'advertiser_id' => 'معلن #',
        'advertiser_name' => 'إسم المعلن',
        'starts_at' => 'تبدء في',
        'ends_at' => 'تنتهي في',
        'purchase_count' => 'عدد مرات الشراء',
        'total_price' => 'مجمل سعر شراء',
        'is_ended' => 'منتهية',
        'is_current' => 'الحالية',
        'is_active' => 'نشط',
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
            'select-option' => 'إختر نوع الحذف: ',
            'soft-delete' => 'حذف جزئي',
            'permanent-delete' => 'حذف نهائي',
            'content' => 'هل أنت متأكد أنك تريد حذف المدفوعات؟',
            'submit' => 'حذف',
            'cancel' => 'إلغاء',
        ],
        'restore' => [
            'title' => 'إستعادة المدفوعات',
            'content' => 'هل أنت متأكد أنك تريد إستعادة المدفوعات؟',
            'submit' => 'إستعادة',
            'cancel' => 'إلغاء',
        ]
    ],
];
