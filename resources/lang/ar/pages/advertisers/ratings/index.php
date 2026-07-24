<?php

return [
    'breadcrumb' => [
        'title' => 'عرض تقييمات المعلنين',
        'home' => 'الصفحة الرئيسية',
        'advertisers' => 'المعلنين',
        'ratings' => 'التقييمات',
        'page' => 'عرض',
    ],
    'content' => [
        'title' => 'عرض تقييمات المعلنين',
        'datatable' => [
            'advertiser_id' => 'معلن #',
            'advertiser_name' => 'إسم المعلن',
            'user_type' => 'نوع المقيم',
            'user_types' => [
                'customer' => 'مستخدم',
                'advertiser' => 'معلن',
            ],
            'user_id' => 'المقيم #',
            'user_name' => 'إسم المُقَيِّم',
            'comment' => 'التعليق',
            'rate' => 'التقييم',
            'status' => 'الحالة',
            'status_types' => [
              'approved' => 'تم الموافقة',
              'pending' => 'جاري المتابعة',
              'unapproved' => 'تم الرفض',
            ],
            'created_at' => 'أنشئ في',
        ],
        'tabs' => [
            'all' => 'الكل (:count)',
            'approved' => 'الموافق عليها (:count)',
            'pending' => 'في الإنتظار (:count)',
            'unapproved' => 'الغير موافق عليها (:count)',
        ]
    ],
    'modal' => [
        'edit' => [
            'title' => 'تعديل التقييم',
            'inputs' => [
                'status' => 'الحالة',
                'approved' => 'موافق عليها',
                'pending' => 'في الإنتظار',
                'unapproved' => 'غير موافق عليها',
                'comment' => 'التعليق',
                'rate' => 'التقييم',
            ],
            'submit' => 'حفظ التعديلات',
            'cancel' => 'إلغاء',
        ],
        'delete' => [
            'title' => 'حذف المعلنين',
            'content' => 'هل أنت متأكد انك تريد حذف هؤلاء المستخدمين؟',
            'submit' => 'حذف',
            'cancel' => 'إلغاء',
        ],
    ]
];
