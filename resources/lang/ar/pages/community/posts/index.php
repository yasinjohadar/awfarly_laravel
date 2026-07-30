<?php

return [
    'breadcrumb' => [
        'title' => 'عرض المنشورات',
        'home' => 'الصفحة الرئيسية',
        'community' => 'المجتمع',
        'posts' => 'المنشورات',
        'page' => 'عرض',
    ],
    'content' => [
        'title' => 'عرض المنشورات',
        'tabs' => [
            'all' => 'كل المنشورات (:count)',
            'unreviewed' => 'منشورات لم تتم مراجعتها (:count)',
            'active' => 'المنشورات النشطة (:count)',
            'deleted' => 'منشورات محذوفة (:count)',
        ]
    ],
    'datatable' => [
        'user_type' => 'نوع المستخدم',
        'user_id' => 'مستخدم #',
        'user_name' => 'إسم المعرف',
        'governorate' => 'المحافظة',
        'city' => 'المدينة',
        'content' => 'المضمون',
        'views_count' => 'مشاهدات',
        'likes_count' => 'إعجابات',
        'comments_count' => 'تعليقات',
        'shares_count' => 'المشاركات',
    ],
    'modal' => [
        'edit' => [
            'title' => 'تعديل المنشورات',
            'inputs' => [
                'governorate' => 'المحافظة',
                'city' => 'المدينة',
                'select_governorate' => 'اختر المحافظة',
                'select_city' => 'اختر المدينة',
                'views_count' => 'عدد المشاهدات',
                'likes_count' => 'عدد الإعجابات',
                'comments_count' => 'عدد التعليقات',
            ],
            'submit' => 'حفظ التعديلات',
            'cancel' => 'إلغاء',
        ],
        'delete' => [
            'title' => 'Delete Posts',
            'select-option' => 'إختر نوع الحذف: ',
            'soft-delete' => 'حذف جزئي',
            'permanent-delete' => 'حذف نهائي',
            'content' => 'هل أنت متأكد أنك تريد حذف هذه المنشورات؟',
            'submit' => 'حذف',
            'cancel' => 'إلغاء',
        ],
        'restore' => [
            'title' => 'إستعادة منشور',
            'content' => 'هل أنت متأكد أنك تريد إستعادة هذا المنشور؟',
            'submit' => 'إستعادة',
            'cancel' => 'إلغاء',
        ],
    ],
];
