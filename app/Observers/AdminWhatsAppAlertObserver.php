<?php

namespace App\Observers;

use App\Helpers\AdminWhatsAppNotifier;
use App\Models\Offers\Comments\OffersComments;
use App\Models\Offers\Offer;
use App\Models\Posts\Comments\PostComments;
use App\Models\Posts\Post;
use App\Models\Reports\Report;
use App\Models\Requests\ContactForms;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Customers\CustomerUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Alerts the admin over WhatsApp when users add offers, posts, comments, reports or
 * contact-us messages. Registered for each of those models in AppServiceProvider.
 *
 * Creation usually happens inside a DB transaction, so the message is built after the
 * response from a fresh copy of the row — a rolled-back insert sends nothing.
 */
class AdminWhatsAppAlertObserver
{
    public function created(Model $model): void
    {
        // Content an admin adds from the panel is not news to the admin.
        if (Auth::guard('admin')->check()) {
            return;
        }

        $class = get_class($model);
        $id = $model->getKey();

        AdminWhatsAppNotifier::notifyLater(function () use ($class, $id) {
            $query = $class::query();
            if (method_exists($class, 'withTrashed')) {
                $query->withTrashed();
            }
            $fresh = $query->find($id);

            return $fresh ? $this->buildMessage($fresh) : null;
        });
    }

    private function buildMessage(Model $model): ?string
    {
        if ($model instanceof Offer) {
            return $this->format('🏷️ عرض جديد', [
                'المعلن' => $this->userName($model->advertiser),
                'الحالة' => $model->status,
                'المحتوى' => $this->excerpt($model->content),
            ], $this->adminUrl('admin.community.offers.show', ['id' => $model->id]));
        }

        if ($model instanceof Post) {
            return $this->format('📝 منشور جديد', [
                'الناشر' => $this->userName($model->user),
                'الحالة' => $model->status,
                'المحتوى' => $this->excerpt($model->content),
            ], $this->adminUrl('admin.community.posts.show', ['post' => $model->id]));
        }

        if ($model instanceof OffersComments) {
            return $this->format($model->comment_id ? '💬 رد جديد على تعليق (عرض)' : '💬 تعليق جديد على عرض', [
                'الكاتب' => $this->userName($model->user),
                'رقم العرض' => $model->offer_id,
                'التعليق' => $this->excerpt($model->comment),
            ], $this->adminUrl('admin.community.offers.comments.show', ['comment' => $model->id]));
        }

        if ($model instanceof PostComments) {
            return $this->format($model->comment_id ? '💬 رد جديد على تعليق (منشور)' : '💬 تعليق جديد على منشور', [
                'الكاتب' => $this->userName($model->user),
                'رقم المنشور' => $model->post_id,
                'التعليق' => $this->excerpt($model->comment),
            ], $this->adminUrl('admin.community.comments.show', ['comment' => $model->id]));
        }

        if ($model instanceof Report) {
            [$label, $route] = $this->reportTarget($model->reported_type);

            return $this->format('🚩 بلاغ جديد', [
                'المُبلِّغ' => $this->userName($model->user),
                'على' => $label . ' #' . $model->reported_id,
                'النوع' => $model->type,
                'السبب' => $this->excerpt($model->reason),
            ], $route ? $this->adminUrl($route) : null);
        }

        if ($model instanceof ContactForms) {
            return $this->format('📩 رسالة تواصل جديدة (' . $model->type . ')', [
                'الاسم' => $model->name,
                'الجوال' => $model->mobile,
                'واتساب' => $model->whatsappMobile,
                'الرسالة' => $this->excerpt($model->message),
            ], $this->adminUrl('admin.requests.contact-us.index'));
        }

        return null;
    }

    /**
     * @return array{0: string, 1: ?string} human label and admin route of the reported list
     */
    private function reportTarget(?string $reportedType): array
    {
        return match ($reportedType) {
            Offer::class => ['عرض', 'admin.community.offers.reports'],
            Post::class => ['منشور', 'admin.community.posts.reports'],
            PostComments::class => ['تعليق منشور', 'admin.community.comments.reports'],
            OffersComments::class => ['تعليق عرض', 'admin.community.offers.comments.reports'],
            AdvertiserUser::class => ['معلن', 'admin.advertisers.reports'],
            CustomerUser::class => ['مستخدم', 'admin.customers.reports'],
            default => [class_basename((string) $reportedType), null],
        };
    }

    private function format(string $title, array $fields, ?string $url): string
    {
        $lines = ['*' . $title . '*'];
        foreach ($fields as $label => $value) {
            if ($value !== null && $value !== '') {
                $lines[] = $label . ': ' . $value;
            }
        }
        if ($url) {
            $lines[] = '';
            $lines[] = $url;
        }

        return implode("\n", $lines);
    }

    private function userName(?Model $user): string
    {
        if (!$user) {
            return '-';
        }

        $name = $user->name ?? '';
        $username = $user->username ?? '';

        return trim($name . ($username !== '' ? ' (@' . $username . ')' : '')) ?: ('#' . $user->getKey());
    }

    private function excerpt(?string $text): string
    {
        return Str::limit(trim(strip_tags((string) $text)), 300);
    }

    private function adminUrl(string $name, array $params = []): ?string
    {
        return Route::has($name) ? route($name, $params) : null;
    }
}
