<?php
return [
	'server' => [
		'listen' => '0.0.0.0',
		'port' => 8080,
	],
    'register' => [
        'listen' => '0.0.0.0',
        'port' => 9500,
    ],
    'dispatchType' => 'http',
    'swoole' => [
        'worker_num' => 5,
        'max_request' => 1000000,
        'log_file' => __DIR__ . '/../runtime/swoole.log',
        'pid_file' => __DIR__ . '/../runtime/swoole.pid'
    ],
    'timerMs' => 1000 * 5 //定时检测 5秒
];