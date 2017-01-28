<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Setting up response data format
    |--------------------------------------------------------------------------
    |
    | Structure of the response that will be followed in each API response.
    | Values should be signatures of preset processors.
    | The following signatures are supported out of box: ":data", ":meta", ":status", ":originalData".
    | If necessary, you can connect and register your processors.
    |
    */
    'format' => [
        'status' => ':status',
        'meta' => ':meta',
        'response' => ':data'
    ],

    /*
    |--------------------------------------------------------------------------
    | Sets information notifications
    |--------------------------------------------------------------------------
    |
    | Defines if the set of messages is fixed (depends on response code) or allows setting notifications freely.
    |
    */
    'messages' => [
        'fixed' => true
    ]
];