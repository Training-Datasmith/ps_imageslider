<?php

error_reporting(-1);
ini_set('display_errors', '1');

$target = isset($argv[1]) ? $argv[1] : '';
$sentinel = isset($argv[2]) ? $argv[2] : '';
$defineVersion = isset($argv[3]) && $argv[3] === '1';

if ($defineVersion) {
    define('_PS_VERSION_', '8.1.7');
}

$includeCompleted = false;
register_shutdown_function(function () use ($sentinel, &$includeCompleted) {
    if ($includeCompleted && $sentinel !== '') {
        file_put_contents($sentinel, 'SENTINEL_AFTER');
    }
});

include $target;
$includeCompleted = true;
