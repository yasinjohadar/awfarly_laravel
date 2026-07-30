<?php

namespace Database\Seeders;

use App\Models\Requests\ContactForms;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ContactFormsSeeder extends Seeder
{
    public function run()
    {
        $types = [
            'Enquiry',
            'Complaint',
            'Suggestion',
            'Payments',
            'Technical support',
            'In-app advertising',
            'Report a problem',
        ];

        $names = [
            'أحمد الخطيب',
            'سارة محمود',
            'محمد العلي',
            'نور الحسن',
            'خالد يوسف',
            'لين أبو زيد',
            'عمر الشامي',
            'رنا الدرزي',
            'يوسف حمود',
            'هدى إبراهيم',
        ];

        $messages = [
            'أرغب بالاستفسار عن باقات الاشتراك المتاحة للمعلنين.',
            'واجهت مشكلة أثناء رفع صورة العرض، الرجاء المساعدة.',
            'أقترح إضافة فلترة أفضل حسب المدينة والمحافظة.',
            'تم خصم مبلغ الاشتراك ولم تتفعل الباقة بعد.',
            'أحتاج دعماً فنياً بخصوص تسجيل الدخول في التطبيق.',
            'أريد الإعلان داخل التطبيق عن منتج جديد.',
            'هناك محتوى مخالف في أحد المنشورات، الرجاء المراجعة.',
            'كيف يمكنني تغيير اسم المستخدم الخاص بي؟',
            'العرض لا يظهر للعملاء رغم الموافقة عليه.',
            'شكراً لكم، التطبيق ممتاز وأتمنى المزيد من الميزات.',
        ];

        for ($i = 1; $i <= 50; $i++) {
            $mobile = '09' . str_pad((string) random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT);
            $status = $i <= 20 ? 'read' : 'unread';

            ContactForms::create([
                'type' => $types[array_rand($types)],
                'name' => $names[array_rand($names)] . ' ' . $i,
                'mobile' => $mobile,
                'whatsappMobile' => $mobile,
                'email' => "contact{$i}@example.com",
                'message' => $messages[array_rand($messages)],
                'status' => $status,
                'created_at' => Carbon::now()->subDays(random_int(0, 40))->subHours(random_int(0, 23)),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
