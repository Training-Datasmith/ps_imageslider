<?php

class InstallConfigTest extends PsImageSliderTestCase
{
    public function testInstallCreatesTablesHooksDefaultsAndSamples()
    {
        Shop::setContext(Shop::CONTEXT_ALL);
        $module = new Ps_ImageSlider();
        $this->assertTrue($module->install());
        $db = Db::getInstance();
        $this->assertNotFalse($db->getRow('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'homeslider` LIKE "id_shop"'));
        $this->assertNotFalse($db->getRow('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'homeslider_slides` LIKE "position"'));
        $this->assertNotFalse($db->getRow('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'homeslider_slides_lang` LIKE "image"'));
        $hooks = $module->registeredHooks;
        sort($hooks);
        $this->assertSame(array('actionShopDataDuplication', 'displayHeader', 'displayHome'), $hooks);
        $this->assertSame('5000', Configuration::get('HOMESLIDER_SPEED', null, null, 1));
        $this->assertSame('1', Configuration::get('HOMESLIDER_PAUSE_ON_HOVER', null, null, 2));
        $this->assertSame('1', Configuration::get('HOMESLIDER_WRAP', null, 7, null));
        $this->assertSame('5000', Configuration::get('HOMESLIDER_SPEED'));
        $rows = $db->executeS('SELECT COUNT(*) AS c FROM `' . _DB_PREFIX_ . 'homeslider_slides`');
        $this->assertSame(3, (int) $rows[0]['c']);
    }

    public function testSampleImagesUseRtlSuffixOnlyForRtlLanguages()
    {
        Shop::setContext(Shop::CONTEXT_SHOP, 1);
        $module = new Ps_ImageSlider();
        $module->install();
        $db = Db::getInstance();
        $rows = $db->executeS('SELECT `id_lang`, `image` FROM `' . _DB_PREFIX_ . 'homeslider_slides_lang` ORDER BY `id_homeslider_slides`, `id_lang`');
        $byLang = array();
        foreach ($rows as $row) {
            $byLang[(int) $row['id_lang']][] = $row['image'];
        }
        foreach ($byLang[1] as $image) {
            $this->assertNotContains('_rtl', $image);
        }
        foreach ($byLang[2] as $image) {
            $this->assertContains('_rtl', $image);
        }
        $positions = $db->executeS('SELECT `position` FROM `' . _DB_PREFIX_ . 'homeslider_slides` ORDER BY `position`');
        $this->assertSame(array(array('position' => '1'), array('position' => '2'), array('position' => '3')), $positions);
    }

    public function testInstallStopsWhenParentInstallFails()
    {
        $module = new Ps_ImageSlider();
        $module->parentInstallResult = false;
        $this->assertFalse($module->install());
        $rows = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'homeslider_slides`');
        $this->assertSame(array(), $rows);
    }

    public function testInstallReturnsFalseWhenConfigUpdateFails()
    {
        Configuration::$failOnKey = 'HOMESLIDER_WRAP';
        $module = new Ps_ImageSlider();
        $this->assertFalse($module->install());
        $rows = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'homeslider_slides`');
        $this->assertSame(array(), $rows);
        Configuration::resetHooks();
    }

    public function testInstallTabCreatesHiddenConfigureSlidesTab()
    {
        $module = new Ps_ImageSlider();
        $this->assertTrue($module->installTab());
        $id = Tab::getIdFromClassName('AdminConfigureSlides');
        $tab = new Tab($id);
        $this->assertSame('AdminConfigureSlides', $tab->class_name);
        $this->assertSame('ps_imageslider', $tab->module);
        $this->assertTrue((bool) $tab->active);
        $this->assertSame(-1, (int) $tab->id_parent);
        foreach (Language::getIDs(false) as $langId) {
            $this->assertArrayHasKey($langId, $tab->name);
        }
    }

    public function testUninstallRemovesTablesConfigAndTab()
    {
        $module = new Ps_ImageSlider();
        $module->install();
        $this->assertTrue($module->uninstall());
        $db = Db::getInstance();
        $this->assertFalse($db->getRow('SHOW TABLES LIKE "' . _DB_PREFIX_ . 'homeslider"'));
        $this->assertFalse(Configuration::get('HOMESLIDER_SPEED'));
        $this->assertSame(0, Tab::getIdFromClassName('AdminConfigureSlides'));
    }

    public function testUninstallStopsWhenParentUninstallFails()
    {
        $module = new Ps_ImageSlider();
        $module->install();
        $module->parentUninstallResult = false;
        $this->assertFalse($module->uninstall());
        $this->assertNotFalse(Db::getInstance()->getRow('SHOW TABLES LIKE "' . _DB_PREFIX_ . 'homeslider"'));
    }

    public function testUninstallTabIsTrueWhenNothingIsLoaded()
    {
        $module = new Ps_ImageSlider();
        $this->assertTrue($module->uninstallTab());
    }
}
