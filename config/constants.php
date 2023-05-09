<?php
return [
    'upload' => [
        'users'=>[
            'folder' => 'storage/users/profiles/',
        ],
        'ticket' => [
            'fileSizeByte' => 8000000,//8M=8000000
            'fileSizeKiloByte' => 8192,//4M=8000000
            'fileSizeSymbol' => '8M', //4M=8000000
            'allowExtensionJS' => '/(\.jpg|\.jpeg)$/i',
            'loadingFile' => '/img/7070.gif',
            'folder' => 'storage/tickets/',
        ],
        'register' => [
            'imageWidth' => 400,
            'imageHeight' => 400,
            'fileSizeByte' => 4000000,//4M=4000000
            'fileSizeKiloByte' => 4096,//4M=4000000
            'fileSizeSymbol' => '4M', //4M=4000000
            'allowExtensionJS' => '/(\.jpg|\.jpeg)$/i',
            'allowExtensionPHP' => 'jpeg,jpg',
            'loadingFile' => '/img/7070.gif',
            'imageFolder' => 'storage/images/',
        ]
    ]
];
