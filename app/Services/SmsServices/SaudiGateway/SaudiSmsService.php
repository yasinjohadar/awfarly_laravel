<?php

namespace App\Services\SmsServices\SaudiGateway;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class SaudiSmsService
{

    public function send($numbers, String $message)
    {
        $endpoint = config('services.sms_gateway.endpoint');
        $key = config('services.sms_gateway.key');
        $client = config('services.sms_gateway.client_id');
        $senderId = config('services.sms_gateway.sender_id');

        $response = Http::get($endpoint, [
            'ApiKey'    =>  $key,
            'ClientId'    =>  $client,
            'SenderId'    =>  $senderId,
            'Message'    =>  $message,
            'MobileNumbers'    =>  Str::replace('+', '', $numbers),
            'Is_Unicode '    =>  true,
        ]);
        $response = json_decode($response->body(), true);

        return $response['ErrorCode'] == '0';
    }
}
