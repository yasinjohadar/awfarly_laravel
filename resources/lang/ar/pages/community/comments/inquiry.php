<?php

return [
    'datatable' => [
        'post_id' => 'المنشور #',
        'user_type' => 'نوع المستخدم',
        'user_id' => 'مستخدم #',
        'user_name' => 'إسم المعرف',
        'comment' => 'التعلييق',
        'deleted_at' => 'تم حذفة في',
        'created_at' => 'أنشئ في',
    ],
    'modal' => [
        'edit' => [
            'title' => 'تعديل التعليقات',
            'inputs' => [
                'parent' => 'القسم الرئيسي',
                'name_en' => 'الإسم باللغة الإنجليزية',
                'name_ar' => 'الإسم باللغة العربية',
                'description' => 'الوصف',
                'image' => 'الصورة',
                'placeholders' => [
                    'choose_file' => 'صورة',
                ]
            ],
            'submit' => 'حفظ التعديلات',
            'cancel' => 'إلغاء',
        ],
        'delete' => [
            'title' => 'حذف التعليقات',
            'select-option'=>'إختر نوع الحذف: ',
            'soft-delete'=>'حذف جزئي',
            'permanent-delete'=>'حذف نهائي',
            'content' => 'هل أنت متأكد انك تريد حذف هذه التعليقات؟',
            'submit' => 'حذف',
            'cancel' => 'إلغاء',
        ],
        'restore' => [
            'title' => 'إستعادة التعليق',
            'content' => 'هل أنت متأكد أنك تريد إستعادة هذا التعليق؟',
            'submit' => 'إستعادة',
            'cancel' => 'إلغاء',
        ]
    ]
];
