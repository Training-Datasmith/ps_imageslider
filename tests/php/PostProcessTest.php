<?php

class PostProcessTest extends PsImageSliderTestCase
{
    private function parseRedirectQuery($url)
    {
        $query = array();
        $fragment = $url;
        if (false !== strpos($url, '?')) {
            $parts = parse_url($url);
            $fragment = isset($parts['query']) ? $parts['query'] : '';
        } elseif (false !== strpos($url, '&')) {
            $fragment = substr($url, strpos($url, '&') + 1);
        }
        parse_str($fragment, $query);

        return $query;
    }

    public function testShopContextSavesIntegerSettingsAndRedirectsWithConf6()
    {
        Shop::setContext(Shop::CONTEXT_SHOP, 1);
        Tools::submitKey('submitSlider');
        $_POST['HOMESLIDER_SPEED'] = '2500';
        $_POST['HOMESLIDER_PAUSE_ON_HOVER'] = '0';
        $_POST['HOMESLIDER_WRAP'] = '1';
        $module = $this->newModule();
        try {
            $module->callPostProcess();
            $this->fail('Expected redirect');
        } catch (RedirectException $e) {
            $query = $this->parseRedirectQuery($e->url);
            $this->assertSame('6', $query['conf']);
            $this->assertSame('ps_imageslider', $query['configure']);
            $this->assertSame('ps_imageslider', $query['module_name']);
        }
        $this->assertSame(
            '2500',
            Configuration::get('HOMESLIDER_SPEED', null, Shop::getContextShopGroupID(), Shop::getContextShopID())
        );
    }

    public function testAllShopsContextWritesGlobalAndGroupScopes()
    {
        Shop::setContext(Shop::CONTEXT_ALL);
        Tools::submitKey('submitSlider');
        $_POST['HOMESLIDER_SPEED'] = '1111';
        $_POST['HOMESLIDER_PAUSE_ON_HOVER'] = '1';
        $_POST['HOMESLIDER_WRAP'] = '0';
        try {
            $this->newModule()->callPostProcess();
        } catch (RedirectException $e) {
        }
        $this->assertSame('1111', Configuration::get('HOMESLIDER_SPEED'));
        $this->assertSame('1111', Configuration::get('HOMESLIDER_SPEED', null, 7, null));
    }

    public function testGroupContextWritesGroupScopeButNotGlobal()
    {
        Configuration::updateValue('HOMESLIDER_SPEED', '9999');
        Shop::setContext(Shop::CONTEXT_GROUP, 7);
        Tools::submitKey('submitSlider');
        $_POST['HOMESLIDER_SPEED'] = '2222';
        $_POST['HOMESLIDER_PAUSE_ON_HOVER'] = '1';
        $_POST['HOMESLIDER_WRAP'] = '1';
        try {
            $this->newModule()->callPostProcess();
        } catch (RedirectException $e) {
        }
        $this->assertSame('2222', Configuration::get('HOMESLIDER_SPEED', null, 7, null));
        $this->assertSame('9999', Configuration::get('HOMESLIDER_SPEED'));
    }

    public function testFailedConfigWriteDoesNotRedirect()
    {
        Configuration::$failOnKey = 'HOMESLIDER_SPEED';
        Tools::submitKey('submitSlider');
        $_POST['HOMESLIDER_SPEED'] = '1000';
        $_POST['HOMESLIDER_PAUSE_ON_HOVER'] = '1';
        $_POST['HOMESLIDER_WRAP'] = '1';
        $module = $this->newModule();
        $module->callPostProcess();
        $this->assertNull(Tools::$lastRedirect);
        Configuration::resetHooks();
    }

    public function testChangeStatusFlipsZeroToOneAndOneToZero()
    {
        $this->insertSlide(100, 1, 1, 0, 1, 'a.jpg');
        Tools::submitKey('changeStatus');
        $_POST['id_slide'] = '100';
        $this->newModule()->callPostProcess();
        $row = Db::getInstance()->getRow('SELECT `active` FROM `' . _DB_PREFIX_ . 'homeslider_slides` WHERE `id_homeslider_slides`=100');
        $this->assertSame('1', $row['active']);
        $this->newModule()->callPostProcess();
        $row = Db::getInstance()->getRow('SELECT `active` FROM `' . _DB_PREFIX_ . 'homeslider_slides` WHERE `id_homeslider_slides`=100');
        $this->assertSame('0', $row['active']);
    }

    public function testDisallowedMimeKeepsThePreviousImage()
    {
        $this->insertSlide(101, 1, 1, 1, 1, 'kept.jpg');
        Tools::submitKey('submitSlide');
        Tools::submitKey('id_slide');
        $_POST['id_slide'] = '101';
        $_POST['active_slide'] = '1';
        $_POST['image_old_1'] = 'kept.jpg';
        $_POST['title_1'] = 't';
        $tmp = tempnam(sys_get_temp_dir(), 'txt');
        file_put_contents($tmp, 'x');
        $_FILES['image_1'] = array('name' => 'note.txt', 'tmp_name' => $tmp, 'error' => 0, 'size' => 1);
        try {
            $this->newModule()->callPostProcess();
        } catch (RedirectException $e) {
        }
        $row = Db::getInstance()->getRow('SELECT `image` FROM `' . _DB_PREFIX_ . 'homeslider_slides_lang` WHERE `id_homeslider_slides`=101 AND `id_lang`=1');
        $this->assertSame('kept.jpg', $row['image']);
        $this->assertFileNotExists(_PS_MODULE_DIR_ . 'images/zz-test-upload.jpg');
        unlink($tmp);
    }

    public function testAddingASlideAssignsMaxPlusOnePosition()
    {
        $this->insertSlide(102, 1, 4, 1, 1, 'a.jpg');
        Tools::submitKey('submitSlide');
        $_POST['active_slide'] = '1';
        $_POST['has_picture'] = '1';
        $_POST['title_1'] = 'new';
        $_POST['image_old_1'] = 'kept.jpg';
        try {
            $this->newModule()->callPostProcess();
        } catch (RedirectException $e) {
            $query = $this->parseRedirectQuery($e->url);
            $this->assertSame('3', $query['conf']);
        }
        $row = Db::getInstance()->getRow('SELECT `position` FROM `' . _DB_PREFIX_ . 'homeslider_slides` ORDER BY `id_homeslider_slides` DESC LIMIT 1');
        $this->assertSame('5', $row['position']);
    }

    public function testDeleteRedirectsWithConf1()
    {
        $this->insertSlide(103, 1, 1, 1, 1, 'a.jpg');
        Tools::submitKey('delete_id_slide');
        $_POST['delete_id_slide'] = '103';
        try {
            $this->newModule()->callPostProcess();
        } catch (RedirectException $e) {
            $query = $this->parseRedirectQuery($e->url);
            $this->assertSame('1', $query['conf']);
        }
        $this->assertFalse(Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'homeslider` WHERE `id_homeslider_slides`=103'));
    }
}
