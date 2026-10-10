<?php

class IndexGuardTest extends PsImageSliderTestCase
{
    private static $indexFiles = array(
        'index.php',
        'translations/index.php',
        'js/index.php',
        'css/index.php',
        'images/index.php',
        'upgrade/index.php',
        'views/index.php',
        'views/templates/index.php',
        'views/templates/hook/index.php',
        'views/templates/admin/index.php',
        'views/templates/admin/_configure/index.php',
        'views/templates/admin/_configure/helpers/index.php',
        'views/templates/admin/_configure/helpers/form/index.php',
        'tests/index.php',
        'tests/php/phpstan/index.php',
    );

    public function testDirectoryIndexScriptsExitBeforeSentinel()
    {
        $root = _PS_MODULE_DIR_;
        foreach (self::$indexFiles as $rel) {
            $this->assertFileExists($root . $rel);
        }

        foreach (self::$indexFiles as $rel) {
            $result = $this->runIncludeWrapper($root . $rel, false);
            $this->assertSame(0, $result['exit']);
            $this->assertFalse(file_exists($result['sentinel']));
            $this->assertNotContains('SENTINEL_AFTER', $result['stdout'] . $result['stderr']);
        }
    }

}
