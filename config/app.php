<?php
return [
    'name' => 'RJSStore',
    'base_url' => env('APP_URL', 'http://localhost/ecommerce/public'),
    'midtrans_server_key' => env('MIDTRANS_SERVER_KEY', ''),
    'midtrans_client_key' => env('MIDTRANS_CLIENT_KEY', ''),
    'midtrans_is_production' => env('MIDTRANS_IS_PRODUCTION', 'false') === 'true',
];
