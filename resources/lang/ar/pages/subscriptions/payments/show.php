<?php

return [
    'content' => [
        'title' => 'عرض المدفوعات <strong>:id</strong>',
        'back' => 'رجوع',
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'payment_id' => 'رقم المدفوعة',
        'package_id' => 'رقم الخطة',
        'package_name' => 'إسم الخطة',
        'advertiser_id' => 'رقم المعلن',
        'advertiser_name' => 'إسم المعلن',
        'starts_at' => 'تبدأ في',
        'ends_at' => 'تنتهي في',
        'remaining' => 'المتبقي',
        'remaining_days' => ':days يوم متبقي',
        'ended_ago' => 'انتهت منذ :days يوم',
        'no_end_date' => 'بدون تاريخ انتهاء',
        'progress' => 'التقدم',
        'timeline_title' => 'مدة الاشتراك',
        'parties_title' => 'الخطة والمعلن',
        'status_title' => 'الحالة',
        'purchase_count' => 'عدد مرات الشراء',
        'is_ended' => 'منتهية',
        'is_current' => 'الحالية',
        'is_active' => 'نشط',
        'deleted_at' => 'تم حذفها في',
        'boolean' => [
            'yes' => 'نعم',
            'no' => 'لا',
        ],
    ],
    'modal' => [
        'edit' => [
            'title' => 'تعديل المدفوعات',
            'inputs' => [
                'starts_at' => 'تبدأ في',
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
            'title' => 'حذف المدفوعة',
            'content' => 'هل أنت متأكد أنك تريد حذف اشتراك الخطة: :name؟',
            'submit' => 'حذف',
            'cancel' => 'إلغاء',
        ],
    ]
];
