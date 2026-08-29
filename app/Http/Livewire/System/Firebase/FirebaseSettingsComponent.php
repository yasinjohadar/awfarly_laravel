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

    // Diagnostics for "same credentials work here but not there" situations.
    // key_fingerprint identifies the actual private key material (never the key
    // itself) so two environments can be compared safely; key_usable proves the
    // key can really produce a signature; server_time exposes clock skew, the
    // other classic cause of an invalid_grant rejection from Google.
    public ?string $key_fingerprint = null;
    public ?bool $key_usable = null;
    public ?string $server_time_utc = null;

    // Paste-the-whole-file field. Strongly preferred over the individual fields
    // below: mixing a freshly-pasted private_key with a stale private_key_id
    // produces a file Google rejects with invalid_grant, because the key id must
    // identify the very key that signs the request. Taking the whole JSON at once
    // makes that mismatch impossible.
    public ?string $input_json = null;

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

        // Pre-fill only the fields that are safe to carry over. private_key_id is
        // deliberately NOT pre-filled: it must always come from the same file as the
        // private_key being pasted, and pre-filling it previously caused a stale id to
        // be paired with a fresh key — which Google rejects as invalid_grant.
        $this->input_project_id = $this->project_id;
        $this->input_client_email = $this->client_email;
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
        $this->key_fingerprint = null;
        $this->key_usable = null;
        $this->server_time_utc = now()->utc()->toDateTimeString() . ' UTC';

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

        $privateKey = $decoded['private_key'] ?? null;
        if (!$privateKey) {
            return;
        }

        // Fingerprint the PUBLIC key derived from this private key, not the private
        // key's raw text: the same key pasted with different whitespace/line endings
        // (or with a trailing newline trimmed) yields different text but the same
        // derived public key. Fingerprinting the text instead would flag harmless
        // formatting as a key mismatch and send diagnosis down the wrong path.
        try {
            $resource = openssl_pkey_get_private($privateKey);
            if ($resource === false) {
                $this->key_usable = false;
                return;
            }

            $details = openssl_pkey_get_details($resource);
            if (is_array($details) && !empty($details['key'])) {
                $this->key_fingerprint = substr(hash('sha256', $details['key']), 0, 16);
            }

            // Prove the key can actually sign — a key mangled in transit may still
            // parse but fail here, and Google would reject it as invalid_grant,
            // indistinguishable from clock skew without this check.
            $signature = '';
            $this->key_usable = openssl_sign('awfarly-key-selftest', $signature, $resource, OPENSSL_ALGO_SHA256);
        } catch (Throwable $e) {
            $this->key_usable = false;
        }
    }

    public function save()
    {
        if (!Auth::guard('admin')->user()->can('settings.edit')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        $pastedJson = trim((string) $this->input_json);

        if ($pastedJson !== '') {
            // Whole-file paste: take every field from the one source, so the key and its
            // id can never come from different files.
            $decoded = json_decode($pastedJson, true);

            if (!is_array($decoded) || empty($decoded['project_id']) || empty($decoded['private_key']) || empty($decoded['client_email']) || empty($decoded['private_key_id'])) {
                $this->alert('error', __('toastr.error'), [
                    'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                    'text' => __('pages/system/firebase.errors.invalid_file'),
                ]);
                return null;
            }

            $projectId = trim($decoded['project_id']);
            $clientEmail = trim($decoded['client_email']);
            $clientId = trim((string) ($decoded['client_id'] ?? ''));
            $privateKeyId = trim($decoded['private_key_id']);
            $privateKey = $decoded['private_key'];
        } else {
            $this->validate([
                'input_project_id' => ['required', 'string'],
                'input_client_email' => ['required', 'string'],
                'input_client_id' => ['nullable', 'string'],
                'input_private_key_id' => ['required', 'string'],
                'input_private_key' => ['required', 'string'],
            ]);

            $projectId = trim($this->input_project_id);
            $clientEmail = trim($this->input_client_email);
            $clientId = trim($this->input_client_id ?? '');
            $privateKeyId = trim($this->input_private_key_id);
            $privateKey = $this->input_private_key;
        }

        // service-account keys are copy-pasted as a single line with literal "\n" sequences
        // when taken out of a minified JSON, so normalize those into real newlines
        $privateKey = trim($privateKey);
        $privateKey = str_replace(["\r\n", "\r", '\\n'], "\n", $privateKey);

        if (!Str::contains($privateKey, 'PRIVATE KEY')) {
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => __('pages/system/firebase.errors.invalid_private_key'),
            ]);
            return null;
        }

        // Reject a key that cannot actually sign before it ever becomes the live
        // credential — otherwise the failure only surfaces later as a confusing
        // invalid_grant rejection on every push.
        $keyResource = openssl_pkey_get_private($privateKey);
        if ($keyResource === false) {
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => __('pages/system/firebase.errors.invalid_private_key'),
            ]);
            return null;
        }

        try {
            $data = [
                'type' => 'service_account',
                'project_id' => $projectId,
                'private_key_id' => $privateKeyId,
                'private_key' => $privateKey,
                'client_email' => $clientEmail,
                'client_id' => $clientId,
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
            $this->input_json = null;
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

        // Auth and Messaging are separate Google services with separate authorization:
        // Auth succeeding does NOT prove push notifications will work. Testing only Auth
        // previously reported a misleading "connected successfully" while every real push
        // was being rejected with invalid_grant. Both are probed now, Messaging included.
        try {
            app('firebase.auth')->listUsers(1);
        } catch (Throwable $e) {
            Log::error('FirebaseSettingsComponent: auth connection test failed', ['exception' => $e->getMessage()]);
            $this->alert('error', __('pages/system/firebase.content.test_connection.failure'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => __('pages/system/firebase.content.test_connection.auth_failed', ['error' => $e->getMessage()]),
            ]);
            return null;
        }

        // Probe Messaging with a deliberately invalid device token: we don't need a real
        // device to learn whether the credentials are authorized for Messaging at all.
        // A rejection naming the *token* means credentials are fine (only the fake token
        // was bad); any other failure (invalid_grant, permission denied) is a real
        // credentials/authorization problem that would break every actual push.
        try {
            app('firebase.messaging')->send(
                CloudMessage::withTarget('token', 'AWFARLY_CONNECTIVITY_PROBE_INVALID_TOKEN')
            );
        } catch (Throwable $e) {
            $messagingError = $e->getMessage();
            $tokenWasRejected = Str::contains(Str::lower($messagingError), [
                'registration token',
                'not a valid fcm',
                'invalid-registration-token',
                'requested entity was not found',
            ]);

            if (!$tokenWasRejected) {
                Log::error('FirebaseSettingsComponent: messaging connection test failed', ['exception' => $messagingError]);
                $this->alert('error', __('pages/system/firebase.content.test_connection.failure'), [
                    'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                    'text' => __('pages/system/firebase.content.test_connection.messaging_failed', ['error' => $messagingError]),
                ]);
                return null;
            }
        }

        $this->alert('success', __('pages/system/firebase.content.test_connection.success'), [
            'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
        ]);
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
