<?php

error_reporting(-1);
ini_set('display_errors', '1');

$sentinel = isset($argv[1]) ? $argv[1] : '';
require_once __DIR__ . '/app_bootstrap.php';

if (!class_exists('Ps_ImageSlider')) {
    fwrite(STDERR, "Ps_ImageSlider class was not loaded\n");
    exit(1);
}

if ($sentinel !== '') {
    file_put_contents($sentinel, 'Ps_ImageSlider');
}
