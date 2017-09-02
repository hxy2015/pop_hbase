<?php
$config = [
    'host' => 'localhost',
    'port' => '20550',
    'alive' => 1,
];

if (is_file(__DIR__ . '/config.local.php')) {
    include(__DIR__ . '/config.local.php');
}

return $config;
