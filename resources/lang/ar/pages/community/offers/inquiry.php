<?php

return [
    'datatable' => [
        'user_id' => 'معلن #',
        'user_name' => 'إسم المعلن',
        'content' => 'المضمون',
        'sale_percentage' => 'نسبة التخفيض',
        'advertisement_url' => 'رابط الإعلان',
        'expires_at' => 'ينتهي في',
        'expires_in' => 'ينتهي خلال (يوم)',
        'deleted_at' => 'تم حذفة في',
        'status' => 'الحالة',
        'rate' => 'التقييم',
        'likes_count' => 'إعجابات',
        'views_count' => 'مشاهدات',
        'comments_count' => 'تعليقات',
    ],
    'modal' => [
        'edit' => [
            'title' => 'تعديل العروض',
            'inputs' => [
                'content' => 'المضمون',
                'sale_percentage' => 'نسبة التخفيض',
                'advertisement_url' => 'رابط الإعلان',
                'expires_at' => 'ينتهي في',
                'expires_in' => 'ينتهي خلال (يوم)',
                'status' => 'الحالة',
                'rate' => 'التقييم',
                'pending' => 'في قائمه الإنتظار',
                'approved' => 'تم الموافقة عليه',
                'views_count' => 'عدد المشاهدات',
                'likes_count' => 'عدد الإعجابات',
                'comments_count' => 'عدد التعليقات',
            ],
            'submit' => 'حفظ التعديلات',
            'cancel' => 'إلغاء',
        ],
        'delete' => [
            'title' => 'حذف العروض',
            'select-option' => 'إختر نوع الحذف: ',
            'soft-delete' => 'حذف جزئي',
            'permanent-delete' => 'حذف نهائي',
            'content' => 'هل أنت متأكد أنك تريد حذف هذه العروض؟',
            'submit' => 'حذف',
            'cancel' => 'إلغاء',
        ],
        'restore' => [
            'title' => 'إسترجاع العرض',
            'content' => 'هل أنت متأكد أنك تريد إستعادة هذا العرض؟',
            'submit' => 'إستعادة',
            'cancel' => 'إلغاء',
        ]
    ]
];
