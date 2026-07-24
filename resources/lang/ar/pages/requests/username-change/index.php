<?php

return [
    'breadcrumb' => [
        'title' => 'عرض طلبات تغيير الإسم',
        'home' => 'الصفحة الرئيسية',
        'requests' => 'الطلبات',
        'contact-us' => 'تغيير الإسم',
        'page' => 'عرض',
    ],
    'content' => [
        'title' => 'عرض طلبات تغيير الإسم',
        'all' => 'الكل (:count)',
        'approved' => 'الموافق عليها (:count)',
        'pending' => 'لم يتم متابعتها (:count)',
        'declined' => 'الغير موافق عليها (:count)',
        'datatable' => [
            'user_type' => 'نوع المستخدم',
            'user_id' => 'مستخدم #',
            'user_name' => 'إسم المعرف',
            'old_username' => 'إسم المعرف الحالي',
            'new_username' => 'إسم المعرف الجديد',
            'reason' => 'السبب',
            'status' => 'الحالة',
            'created_at' => 'أنشئ في',
            'approved' => 'تمت الموافقة',
            'declined' => 'تم الرفض',
            'pending' => 'يتم المتابعة',
            'types' => [
                'customer' => 'مستخدم',
                'advertiser' => 'معلن',
            ],
        ],
    ],
    'modal' => [
        'approved' => [
            'title' => 'الموافقه علي تغيير إسم المعرف',
            'content' => 'هل أنت متأكد أنك تريد الموافقه علي تغيير إسم المعرف',
            'submit' => 'تأكيد',
            'cancel' => 'إلغاء',
        ],
        'declined' => [
            'title' => 'رفض تغيير إسم المعرف',
            'content' => 'هل أنت متأكد أنك تريد رفض تغيير إسم المعرف',
            'submit' => 'تأكيد',
            'cancel' => 'إلغاء',
        ],
    ]
];
