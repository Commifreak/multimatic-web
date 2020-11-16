<?php

$params = require __DIR__ . '/params.php';
$db     = $params['env'] == 'dev' ? require __DIR__ . '/db_dev.php' : require __DIR__ . '/db.php';

$config = [
    'id'                  => 'basic-console',
    'basePath'            => dirname(__DIR__),
    'bootstrap'           => ['log'],
    'controllerNamespace' => 'app\commands',
    'aliases'             => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@tests' => '@app/tests',
    ],
    'components'          => [
        'cache'       => [
            'class' => 'yii\caching\FileCache',
        ],
        'mailer'      => [
            'class'            => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => false,
            'transport'        => [
                'class'      => 'Swift_SmtpTransport',
                'host'       => '',  // e.g. smtp.mandrillapp.com or smtp.gmail.com
                'username'   => '',
                'password'   => '',
                'port'       => '587', // Port 25 is a very common port too
                'encryption' => 'tls', // It is often used, check your provider or mail server specs
            ],
        ],
        'log'         => [
            'targets' => [
                [
                    'class'   => 'yii\log\FileTarget',
                    'levels'  => ['error', 'warning'],
                    'logFile' => '@runtime/logs/console.log'
                ],
                [
                    'class'      => 'yii\log\FileTarget',
                    'categories' => ['harvester', 'VaillantAPI'],
                    'levels'     => ['error', 'warning', 'info'],
                    'logVars'    => [],
                    'logFile'    => '@runtime/logs/harvester.log'
                ],
                [
                    'class'   => 'yii\log\EmailTarget',
                    'mailer'  => 'mailer',
                    'levels'  => ['error'],
                    'except'  => ['harvester', 'VaillantAPI'],
                    'message' => [
                        'from'    => ['log@multimaticweb.net'],
                        'to'      => ['contact@multimaticweb.net'],
                        'subject' => 'Error occured!',
                    ],
                ],
            ],
        ],
        'db'          => $db,
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            // uncomment if you want to cache RBAC items hierarchy
            // 'cache' => 'cache',
        ],
    ],
    'params'              => $params,
    /*
    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][]    = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
