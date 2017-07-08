<?php return [
    'packages' => [
        'barryvdh' => [
            'providers' => [
                '\Barryvdh\Snappy\ServiceProvider',
            ],

            'aliases' => [
                'SnappyPDF'   => '\Barryvdh\Snappy\Facades\SnappyPdf',
                'SnappyImage' => '\Barryvdh\Snappy\Facades\SnappyImage',
            ],

            'config_namespace' => 'snappy',

            'config' => [
                'pdf' => array(
                    'enabled' => true,
                    'binary'  => env('SNAPPY_PDF_BINARY', plugins_path('luketowers/snappypdf/vendor/bin/wkhtmltopdf-amd64')),
                    'timeout' => false,
                    'options' => array(),
                    'env'     => array(),
                ),
                'image' => array(
                    'enabled' => true,
                    'binary'  => env('SNAPPY_IMAGE_BINARY', plugins_path('luketowers/snappypdf/vendor/bin/wkhtmltoimage-amd64')),
                    'timeout' => false,
                    'options' => array(),
                    'env'     => array(),
                ),
            ],
        ],
    ],
];