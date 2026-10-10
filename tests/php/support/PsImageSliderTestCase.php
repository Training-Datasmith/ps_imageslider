<?php

class PsImageSliderTestCase extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        error_reporting(-1);
        Configuration::resetHooks();
        Db::resetInstance();
        Db::getInstance();
        Schema::dropAll();
        Schema::createAll();
        Schema::resetConfiguration();
        Tab::resetStorage();
        Shop::resetState();
        Context::reset();
        Tools::resetRequest();
        $this->cleanupGeneratedImages();
    }

    protected function tearDown()
    {
        $this->cleanupGeneratedImages();
        Db::$executeSOverride = null;
        Tools::resetRequest();
        Shop::resetState();
        Context::reset();
    }

    protected function cleanupGeneratedImages()
    {
        $dir = _PS_MODULE_DIR_ . 'images/';
        foreach (glob($dir . 'zz-test-*') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    protected function newModule()
    {
        return new TestablePs_ImageSlider();
    }

    protected function runIncludeWrapper($target, $defineVersion, $loadStubs)
    {
        $sentinel = tempnam(sys_get_temp_dir(), 'idxsent');
        if (file_exists($sentinel)) {
            unlink($sentinel);
        }
        $wrapper = __DIR__ . '/../include_wrapper.php';
        $cmd = escapeshellarg(PHP_BINARY) . ' -d error_reporting=-1 ' . escapeshellarg($wrapper) . ' '
            . escapeshellarg($target) . ' ' . escapeshellarg($sentinel) . ' '
            . ($defineVersion ? '1' : '0') . ' ' . ($loadStubs ? '1' : '0');
        $descriptors = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
        $proc = proc_open($cmd, $descriptors, $pipes);
        $this->assertTrue(is_resource($proc));
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exit = proc_close($proc);

        return array('exit' => $exit, 'stdout' => $stdout, 'stderr' => $stderr, 'sentinel' => $sentinel);
    }

    protected function insertSlide($id, $shopId, $position, $active, $langId, $image, $url = '')
    {
        $db = Db::getInstance();
        $db->execute('INSERT INTO `' . _DB_PREFIX_ . 'homeslider_slides` (`id_homeslider_slides`, `position`, `active`)
            VALUES (' . (int) $id . ', ' . (int) $position . ', ' . (int) $active . ')
            ON DUPLICATE KEY UPDATE `position`=' . (int) $position . ', `active`=' . (int) $active);
        $db->execute('INSERT INTO `' . _DB_PREFIX_ . 'homeslider` (`id_homeslider_slides`, `id_shop`)
            VALUES (' . (int) $id . ', ' . (int) $shopId . ')
            ON DUPLICATE KEY UPDATE `id_shop`=' . (int) $shopId);
        $db->execute('REPLACE INTO `' . _DB_PREFIX_ . 'homeslider_slides_lang`
            (`id_homeslider_slides`, `id_lang`, `title`, `description`, `legend`, `url`, `image`)
            VALUES (' . (int) $id . ', ' . (int) $langId . ', "t' . (int) $id . '", "d", "l", "' . $db->escape($url) . '", "' . $db->escape($image) . '")');
    }
}
