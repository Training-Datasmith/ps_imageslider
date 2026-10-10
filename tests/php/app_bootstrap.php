<?php

error_reporting(-1);
ini_set('display_errors', '1');

if (!defined('_PS_VERSION_')) {
    define('_PS_VERSION_', '8.1.7');
}
define('_DB_PREFIX_', 'ps_');
define('_MYSQL_ENGINE_', 'InnoDB');
define('_PS_USE_SQL_SLAVE_', false);
define('_PS_TMP_IMG_DIR_', sys_get_temp_dir());
define('_MODULE_DIR_', '/modules/');

$moduleRoot = dirname(dirname(__DIR__));
define('_PS_MODULE_DIR_', $moduleRoot . '/');

require_once __DIR__ . '/bridge/RedirectException.php';
require_once __DIR__ . '/bridge/WidgetInterface.php';
require_once __DIR__ . '/bridge/Validate.php';
require_once __DIR__ . '/bridge/Db.php';
require_once __DIR__ . '/bridge/Configuration.php';
require_once __DIR__ . '/bridge/Language.php';
require_once __DIR__ . '/bridge/Shop.php';
require_once __DIR__ . '/bridge/Context.php';
require_once __DIR__ . '/bridge/Link.php';
require_once __DIR__ . '/bridge/Tools.php';
require_once __DIR__ . '/bridge/ImageManager.php';
require_once __DIR__ . '/bridge/HelperForm.php';
require_once __DIR__ . '/bridge/Tab.php';
require_once __DIR__ . '/bridge/ObjectModel.php';
require_once __DIR__ . '/bridge/Module.php';
require_once __DIR__ . '/bridge/ModuleAdminController.php';

require_once $moduleRoot . '/classes/Ps_HomeSlide.php';
require_once $moduleRoot . '/ps_imageslider.php';
require_once $moduleRoot . '/controllers/admin/AdminConfigureSlidesController.php';
