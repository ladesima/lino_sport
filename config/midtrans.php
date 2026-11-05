<?php

return [
    'server_key' => env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-DemoKey12345678'),
    'client_key' => env('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-DemoKey12345678'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized' => true,
    'is_3ds' => true,
];
