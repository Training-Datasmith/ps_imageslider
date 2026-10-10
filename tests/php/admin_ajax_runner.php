<?php

error_reporting(-1);
ini_set('display_errors', '1');

require_once __DIR__ . '/app_bootstrap.php';

$cacheMarker = getenv('PS_TEST_CACHE_MARKER');
if ($cacheMarker === false) {
    $cacheMarker = '';
}

$action = isset($argv[1]) ? $argv[1] : '';
$slides = isset($argv[2]) ? $argv[2] : '';

$_GET['action'] = $action;
if ($slides === '__EMPTY__') {
    $_GET['slides'] = array();
} elseif ($slides !== '') {
    $_GET['slides'] = explode(',', $slides);
}

class AjaxTestModule extends Ps_ImageSlider
{
    public function clearCache()
    {
        parent::clearCache();
        $marker = getenv('PS_TEST_CACHE_MARKER');
        if ($marker && $this->lastClearedTemplate) {
            file_put_contents($marker, $this->lastClearedTemplate);
        }
    }
}

$controller = new AdminConfigureSlidesController();
$controller->module = new AjaxTestModule();
ob_start();
$controller->ajaxProcessUpdateSlidesPosition();
$payload = ob_get_clean();
fwrite(STDOUT, $payload);
