<?php
$servers = [];
if(file_exists(__DIR__ . '/servers.php')){
    $servers = require __DIR__ . '/servers.php';
}

return [
    'app' => [],
    'servers' => $servers,
    'sapps' => require __DIR__ . '/sapps.php',
];