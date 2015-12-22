<?php
$sae = defined('SAE_MYSQL_HOST_M');

$db = $sae ? [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host='.SAE_MYSQL_HOST_M.';port='.SAE_MYSQL_PORT.';dbname='.SAE_MYSQL_DB,
            'username' => SAE_MYSQL_USER,
            'password' => SAE_MYSQL_PASS,
            'charset' => 'utf8',
        ] : [
    //'class' => 'yii\db\Connection',
    //'dsn' => 'mysql:host=mysql;dbname=yii2advanced',
    //'username' => 'root',
    //'password' => 'root',
    //'charset' => 'utf8',
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=119.29.36.147;dbname=wetrip',
    'username' => 'root',
    'password' => 'womenxing2014',
    'charset' => 'utf8',
        ];

$components = [
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            //'defaultRoles' => ['guest'],
        ],
        'cache' => [
            'class' => 'yii\caching\DbCache',
        ],
        'db' => $db,
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    //'basePath' => '@app/messages',
                    //'sourceLanguage' => 'en',
                    'fileMap' => [
                        'app' => 'app.php',
                        'app/error' => 'error.php',
                    ],
                ],
                /*'yii' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'zh-CN',
                    'basePath' => '@app/messages'
                ],*/
            ],
        ],
        'formatter' => [
            'dateFormat' => 'yyyy-MM-dd',
            'datetimeFormat' => 'yyyy-MM-dd HH:mm:ss',
            'decimalSeparator' => ',',
            'thousandSeparator' => ' ',
            'currencyCode' => 'CNY',
        ],
    ];

if($sae){
    $components['assetManager'] = [
        'class'=>'postor\sae\SaeAssetManager',
        'assetDomain'=>'assets',
        'converter' => [
            'class' => 'yii\web\AssetConverter',
        ],
    ];
    $components['log'] = [
        'traceLevel' => YII_DEBUG ? 3 : 0,
        'targets' => [
            [
                'class' => 'yii\log\DbTarget',
                'levels' => ['error', 'warning'],
            ],
        ],
    ];
}

$config = [
    'name' => 'Yii2-Adminlte',
    'language' => 'zh-CN',
    'timeZone' => 'Asia/Shanghai',
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => $components,
];


if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = $sae ? [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['192.168.*.*'],
        // 'dataPath' => 'saekv://debug/',//'saestor://assets/debug/',//SAE_TMP_PATH.''
    ] : [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['*.*.*.*'],
    ];
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['192.168.*.*'],
    ];
}
return $config;