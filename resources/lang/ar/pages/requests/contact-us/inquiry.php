<?php

return [
    'content' => [
        'title' => 'عرض طلب رقم #<strong>:id</strong>',
        'back' => 'الرجوع',
        'read' => 'حدد كمقروءة',
        'type' => 'النوع',
        'name' => 'الإسم',
        'mobile' => 'رقم الهاتف',
        'whatsapp_mobile' => 'رقم الواتساب',
        'email' => 'البريد الإلكتروني',
        'message' => 'الرسالة',
        'status' => 'الحالة',
        'created_at' => 'أنشئ في',
        'types' => [
            'Enquiry' => 'استفسار',
            'Complaint' => 'شكوى',
            'Suggestion' => 'اقتراح',
            'Payments' => 'المدفوعات',
            'Technical support' => 'الدعم الفني',
            'In-app advertising' => 'الإعلان في التطبيق',
            'Report a problem' => 'الإبلاغ عن مشكلة',
        ],
        'actions' => [
            'mark_read' => 'تحديد كمقروء',
            'mark_unread' => 'تحديد كغير مقروء',
            'delete' => 'حذف الطلب',
            'call' => 'اتصال',
            'whatsapp' => 'واتساب',
            'email' => 'إرسال بريد',
        ],
        'status_labels' => [
            'read' => 'مقروء',
            'unread' => 'غير مقروء',
        ],
        'sections' => [
            'contact_info' => 'بيانات التواصل',
            'message' => 'نص الرسالة',
        ],
    ],
    'modal' => [
        'confirm' => [
            'title' => [
                'read' => 'حدد الرسالة كمقروءة',
                'unread' => 'حدد الرسالة كغير مقروءة',
            ],
            'content' => [
                'read' => 'هل انت متأكد انك تريد تحديد هذه الرسالة كرسالة مقروءة؟',
                'unread' => 'هل انت متأكد انك تريد تحديد هذه الرسالة كرسالة  غيرمقروءة؟',
            ],
            'submit' => 'تأكيد',
            'cancel' => 'إلغاء',
        ],
        'delete' => [
            'title' => 'حذف طلبات الاتصال بنا',
            'content' => 'هل أنت متأكد أنك تريد حذف هذه الطلبات؟',
            'content_single' => 'هل أنت متأكد أنك تريد حذف هذا الطلب؟',
            'submit' => 'حذف',
            'cancel' => 'إلغاء',
        ],
    ],
];
