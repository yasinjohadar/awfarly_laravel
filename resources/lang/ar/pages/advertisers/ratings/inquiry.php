<?php

return [
    'content' => [
        'title' => 'عرض التقييم #<strong>:id</strong>',
        'back' => 'رجوع',
        'rating_id' => 'تقييم #',
        'advertiser_id' => 'معلن #',
        'advertiser_name' => 'إسم المعلن',
        'user_type' => 'نوع المقيم',
        'user_types' => [
            'customer' => 'مستخدم',
            'advertiser' => 'معلن',
        ],
        'user_id' => 'المقيم #',
        'user_name' => 'إسم المُقَيِّم',
        'comment' => 'التعلييق',
        'rate' => 'التقييم',
        'status' => 'الحالة',
        'status_types' => [
            'approved' => 'تم الموافقة',
            'pending' => 'جاري المتابعة',
            'unapproved' => 'تم الرفض',
        ],
        'created_at' => 'أنشئ في',
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
            'title' => 'حذف الملفات',
            'content' => 'هل أنت متأكد انك تريد حذف هذه الملفات؟',
            'submit' => 'حذف',
            'cancel' => 'إلغاء',
        ],
    ]
];
