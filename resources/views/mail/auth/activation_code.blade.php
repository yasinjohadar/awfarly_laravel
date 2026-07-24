@component('mail::message')
# We sending you this email to reset your account's password.

<p>Activation code is : <strong>{{ $code }}</strong></p>

Thanks,<br>
{{ config('app.name') }}
@endcomponent
