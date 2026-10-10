<?php

error_reporting(-1);
ini_set('display_errors', '1');

$sentinel = isset($argv[1]) ? $argv[1] : '';
require_once __DIR__ . '/app_bootstrap.php';

if ($sentinel !== '') {
    file_put_contents($sentinel, 'SENTINEL_AFTER');
}
