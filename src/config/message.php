<?php

return [
    'vendor' => env('SMS_VENDOR', 'smsgatewayme'),

    /*
     * Send message in async mode. To enable sync mode, you must setup queue job Laravel.
     *
     * @link https://laravel.com/docs/5.6/queues
     */
    'async' => false,

    /*
     * Set configuration from smsgateway.me.
     */
    'smsgatewayme' => [
        'device' => env('SMSGATEWAYME_DEVICE'),
        'token'  => env('SMSGATEWAYME_TOKEN'),
    ],

    /*
     * Set configuration from Zenziva.
     *
     * @link https://reguler.zenziva.net/apps/api.php
     */
    'zenziva' => [
        'userkey' => env('ZENZIVA_USERKEY'),
        'passkey' => env('ZENZIVA_PASSKEY'),
    ],
];
