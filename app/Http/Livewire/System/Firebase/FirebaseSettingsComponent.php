<?php

namespace App\Http\Livewire\System\Firebase;

use App\Helpers\Settings;
use App\Models\Settings\Setting;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Livewire\Component;
use Throwable;

class FirebaseSettingsComponent extends Component
{
    use LivewireAlert;

    public ?string $currentPath = null;
    public ?string $project_id = null;
    public ?string $client_email = null;
    public ?string $private_key_id = null;

    // manual entry fields for updating the credentials
    public ?string $input_project_id = null;
    public ?string $input_client_email = null;
    public ?string $input_client_id = null;
    public ?string $input_private_key_id = null;
    public ?string $input_private_key = null;

    public ?string $test_token = null;
    public ?string $test_title = null;
    public ?string $test_body = null;

    public function mount()
    {
        $this->currentPath = Settings::Get('firebase.credentials.file');
        $this->loadCredentialInfo();

        // pre-fill the non-secret fields from the active credentials so the admin only
        // has to paste the private key + its id when rotating credentials
        $this->input_project_id = $this->project_id;
        $this->input_client_email = $this->client_email;
        $this->input_private_key_id = $this->private_key_id;
    }

    public function render()
    {
        return view('livewire.pages.system.firebase.firebase-settings-component');
    }

    /**
     * Read the metadata (never the private key itself) out of the active credentials file.
     */
    protected function loadCredentialInfo(): void
    {
        $this->project_id = null;
        $this->client_email = null;
        $this->private_key_id = null;

        if (!$this->currentPath || !is_file($this->currentPath)) {
            return;
        }

        $decoded = json_decode((string) file_get_contents($this->currentPath), true);
        if (!is_array($decoded)) {
            return;
        }

        $this->project_id = $decoded['project_id'] ?? null;
        $this->client_email = $decoded['client_email'] ?? null;
        $this->private_key_id = $decoded['private_key_id'] ?? null;
    }

    public function save()
    {
        if (!Auth::guard('admin')->user()->can('settings.edit')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        $this->validate([
            'input_project_id' => ['required', 'string'],
            'input_client_email' => ['required', 'string'],
            'input_client_id' => ['nullable', 'string'],
            'input_private_key_id' => ['required', 'string'],
            'input_private_key' => ['required', 'string'],
        ]);

        // service-account keys are copy-pasted as a single line with literal "\n" sequences
        // when taken out of a minified JSON, so normalize those into real newlines
        $privateKey = trim($this->input_private_key);
        $privateKey = str_replace(["\r\n", "\r", '\\n'], "\n", $privateKey);

        if (!Str::contains($privateKey, 'PRIVATE KEY')) {
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => __('pages/system/firebase.errors.invalid_private_key'),
            ]);
            return null;
        }

        try {
            $clientEmail = trim($this->input_client_email);

            $data = [
                'type' => 'service_account',
                'project_id' => trim($this->input_project_id),
                'private_key_id' => trim($this->input_private_key_id),
                'private_key' => $privateKey,
                'client_email' => $clientEmail,
                'client_id' => trim($this->input_client_id ?? ''),
                'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
                'token_uri' => 'https://oauth2.googleapis.com/token',
                'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
                'client_x509_cert_url' => 'https://www.googleapis.com/robot/v1/metadata/x509/' . rawurlencode($clientEmail),
                'universe_domain' => 'googleapis.com',
            ];

            $contents = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            $relativePath = 'firebase/' . Str::random(40) . '.json';
            Storage::disk('local')->put($relativePath, $contents);
            $absolutePath = Storage::disk('local')->path($relativePath);

            $old = $this->currentPath;

            $setting = Setting::firstOrNew(['key' => 'firebase.credentials.file']);
            if (!$setting->exists) {
                $setting->name = 'Firebase Credentials File';
                $setting->type = 'general';
                $setting->value_type = 'string';
            }
            $setting->value = $absolutePath;
            $setting->save();

            config(['firebase.projects.app.credentials.file' => $absolutePath]);

            // only ever delete a file previously uploaded through this page, never the .env-referenced original
            // (paths are compared with normalized slashes since Storage::path() mixes separators on Windows)
            if ($old && $old !== $absolutePath && Str::contains(str_replace('\\', '/', $old), '/firebase/') && is_file($old)) {
                @unlink($old);
            }

            $this->currentPath = $absolutePath;
            $this->input_private_key = null;
            $this->loadCredentialInfo();

            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
        } catch (Throwable $e) {
            Log::error('FirebaseSettingsComponent: failed to save credentials', ['exception' => $e->getMessage()]);
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);
        }
    }

    public function testConnection()
    {
        if (!Auth::guard('admin')->user()->can('settings.edit')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        try {
            app('firebase.auth')->listUsers(1);

            $this->alert('success', __('pages/system/firebase.content.test_connection.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
        } catch (Throwable $e) {
            Log::error('FirebaseSettingsComponent: connection test failed', ['exception' => $e->getMessage()]);
            $this->alert('error', __('pages/system/firebase.content.test_connection.failure'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);
        }
    }

    public function sendTestNotification()
    {
        if (!Auth::guard('admin')->user()->can('settings.edit')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        $this->validate([
            'test_token' => ['required', 'string'],
            'test_title' => ['nullable', 'string'],
            'test_body' => ['nullable', 'string'],
        ]);

        try {
            $messaging = app('firebase.messaging');

            $notification = FirebaseNotification::create(
                $this->test_title ?: __('pages/system/firebase.content.test_notification.default_title'),
                $this->test_body ?: __('pages/system/firebase.content.test_notification.default_body')
            );

            $message = CloudMessage::withTarget('token', $this->test_token)
                ->withNotification($notification);

            $messaging->send($message);

            $this->alert('success', __('pages/system/firebase.content.test_notification.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
        } catch (Throwable $e) {
            Log::error('FirebaseSettingsComponent: test notification failed', ['exception' => $e->getMessage()]);
            $this->alert('error', __('pages/system/firebase.content.test_notification.failure'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);
        }
    }
}
