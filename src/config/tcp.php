<?php
return [
	'server' => [
		'listen' => '0.0.0.0',
		'port' => 8080,
        'options' => [
            'worker_num' => 5,
            'max_request' => 1000000,
            'log_file' => SUMMER_APP_ROOT . 'runtime/swoole.log',
            'pid_file' => SUMMER_APP_ROOT . 'runtime/swoole.pid'
        ],
	],
    'register' => [
        'listen' => '0.0.0.0',
        'port' => 9500,
    ],
    'dispatchType' => 'http',
    'timerMs' => 1000 * 5 //定时检测
];