<?php

class GetContentBranchTest extends PsImageSliderTestCase
{
    public function testAddSlideInGroupContextDoesNotRenderTheSlideForm()
    {
        Shop::setContext(Shop::CONTEXT_GROUP, 7);
        Tools::submitKey('addSlide');
        $html = $this->newModule()->getContent();
        $this->assertContains('alert-danger', $html);
        $this->assertNotContains('name="active_slide"', $html);
    }

    public function testEditOfSharedSlideIncludesWarningAndForm()
    {
        $this->insertSlide(160, 1, 1, 1, 1, 'a.jpg');
        Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'homeslider` (`id_homeslider_slides`, `id_shop`) VALUES (160, 2)');
        Shop::setContext(Shop::CONTEXT_SHOP, 1);
        Tools::submitKey('id_slide');
        $_POST['id_slide'] = '160';
        $html = $this->newModule()->getContent();
        $this->assertContains('alert-warning', $html);
        $this->assertContains('name="active_slide"', $html);
    }

    public function testDisplayHeaderRegistersCssAndBothScripts()
    {
        $module = $this->newModule();
        $module->hookdisplayHeader(array());
        $paths = array();
        foreach ($module->context->controller->registeredAssets as $asset) {
            if (isset($asset['path'])) {
                $paths[] = $asset['path'];
            }
        }
        $this->assertContains('modules/ps_imageslider/css/homeslider.css', $paths);
        $this->assertContains('modules/ps_imageslider/js/responsiveslides.min.js', $paths);
        $this->assertContains('modules/ps_imageslider/js/homeslider.js', $paths);
    }
}
