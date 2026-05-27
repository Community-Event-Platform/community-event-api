<?php

/**
 * VNPay Configuration
 *
 * Documentation: https://sandbox.vnpayment.vn/apis/docs/bank-of-offices/
 */

return [
    /*
    |--------------------------------------------------------------------------
    | VNPay Environment
    |--------------------------------------------------------------------------
    | 'sandbox' for testing, 'production' for live
    */
    'env' => env('VNPAY_ENV', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | VNPay Credentials
    |--------------------------------------------------------------------------
    */
    'merchant_id' => env('VNPAY_MERCHANT_ID', ''),
    'merchant_password' => env('VNPAY_MERCHANT_PASSWORD', ''),
    'terminal_id' => env('VNPAY_TERMINAL_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | VNPay URLs
    |--------------------------------------------------------------------------
    */
    'urls' => [
        'sandbox' => [
            'payment' => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
            'query' => 'https://sandbox.vnpayment.vn/merchant_webapi/merchant.html',
            'refund' => 'https://sandbox.vnpayment.vn/merchant_webapi/merchant.html',
        ],
        'production' => [
            'payment' => 'https://pay.vnpayment.vn/paymentv2/vpcpay.html',
            'query' => 'https://merchant.vnpayment.vn/paymentv2/merchant_webapi/merchant.html',
            'refund' => 'https://merchant.vnpayment.vn/paymentv2/merchant_webapi/merchant.html',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Response URLs
    |--------------------------------------------------------------------------
    */
    'return_url' => env('APP_URL') . '/api/payment/vnpay/return',
    'ipn_url' => env('APP_URL') . '/api/payment/vnpay/ipn',

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    */
    'default_currency' => 'VND',
    'default_locale' => 'vn',
    'default_version' => '2.1.0',
];
