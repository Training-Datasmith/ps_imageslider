<?php

class WidgetVariablesTest extends PsImageSliderTestCase
{
    public function testMapsSpeedPauseAndWrap()
    {
        Tools::resetRequest();
        Configuration::updateValue('HOMESLIDER_SPEED', 4200);
        Configuration::updateValue('HOMESLIDER_PAUSE_ON_HOVER', 1);
        Configuration::updateValue('HOMESLIDER_WRAP', 0);
        $vars = $this->newModule()->getWidgetVariables();
        $this->assertSame('4200', (string) $vars['homeslider']['speed']);
        $this->assertSame('hover', $vars['homeslider']['pause']);
        $this->assertSame('false', $vars['homeslider']['wrap']);

        Tools::resetRequest();
        Configuration::updateValue('HOMESLIDER_PAUSE_ON_HOVER', 0);
        Configuration::updateValue('HOMESLIDER_WRAP', 1);
        $vars2 = $this->newModule()->getWidgetVariables();
        $this->assertSame('', $vars2['homeslider']['pause']);
        $this->assertSame('true', $vars2['homeslider']['wrap']);
    }

    public function testOnlyActiveSlidesAreExposed()
    {
        $this->insertSlide(80, 1, 1, 1, 1, 'a.jpg');
        $this->insertSlide(81, 1, 2, 0, 1, 'b.jpg');
        $vars = $this->newModule()->getWidgetVariables();
        $this->assertCount(1, $vars['homeslider']['slides']);
        $this->assertSame(80, (int) $vars['homeslider']['slides'][0]['id_slide']);
    }

    public function testSizeAttributeMatchesTheSampleImage()
    {
        $this->insertSlide(82, 1, 1, 1, 1, 'sample-1.jpg');
        $vars = $this->newModule()->getWidgetVariables();
        $slide = $vars['homeslider']['slides'][0];
        $expected = getimagesize(_PS_MODULE_DIR_ . 'images/sample-1.jpg');
        $this->assertTrue(is_array($expected));
        $this->assertSame($expected[3], $slide['size']);
        $this->assertSame((int) $expected[0], (int) $slide['sizes'][0]);
        $this->assertSame((int) $expected[1], (int) $slide['sizes'][1]);
    }

    public function testMissingImageLeavesSizeUnset()
    {
        $this->insertSlide(83, 1, 1, 1, 1, 'zz-test-missing.jpg');
        $vars = $this->newModule()->getWidgetVariables();
        $slide = $vars['homeslider']['slides'][0];
        $this->assertFalse(array_key_exists('size', $slide));
    }

    public function testRenderWidgetAssignsOnlyOnCacheMiss()
    {
        $module = $this->newModule();
        Configuration::updateValue('HOMESLIDER_PAUSE_ON_HOVER', 1);
        $this->insertSlide(84, 1, 1, 1, 1, 'sample-1.jpg');
        $module->cacheHit = false;
        $out = $module->renderWidget();
        $this->assertSame('WIDGET:hover', $out);
        $module->cacheHit = true;
        $module->smarty->assigned = array();
        $out2 = $module->renderWidget();
        $this->assertSame('WIDGET:none', $out2);
    }

    public function testClearCacheTargetsTheSliderTemplate()
    {
        $module = $this->newModule();
        $module->callClearCacheRecord();
        $this->assertSame('module:ps_imageslider/views/templates/hook/slider.tpl', $module->lastClearedTemplate);
    }
}
