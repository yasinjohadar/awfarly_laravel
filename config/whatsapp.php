<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp / Evolution API Configuration
    |--------------------------------------------------------------------------
    | Connection credentials (base URL, API key, default instance) are NOT here —
    | they're admin-editable, stored as `settings` rows (type=whatsapp), same
    | convention as firebase.credentials.file. See EvolutionService::getSettings().
    */

    'timeout' => env('WHATSAPP_TIMEOUT', 30),

    'evolution_connect_timeout' => env('EVOLUTION_API_CONNECT_TIMEOUT', 30),
];
