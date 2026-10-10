<?php

class HeaderStatusFieldsTest extends PsImageSliderTestCase
{
    public function testHeaderHtmlIsEmptyOffTheConfigurationScreen()
    {
        $module = $this->newModule();
        $_GET['controller'] = 'AdminProducts';
        $this->assertNull($module->headerHTML());
        $_GET['controller'] = 'AdminModules';
        $_GET['configure'] = 'other';
        $this->assertNull($module->headerHTML());
    }

    public function testHeaderHtmlIdentifiesTheSortableEndpoint()
    {
        $_GET['controller'] = 'AdminModules';
        $_GET['configure'] = 'ps_imageslider';
        $html = $this->newModule()->headerHTML();
        $this->assertContains('new Sortable', $html);
        $this->assertContains('#slides', $html);
        $this->assertContains('updateSlidesPosition', $html);
        $this->assertContains('AdminConfigureSlides', $html);
    }

    public function testInactiveStatusUsesDangerIcon()
    {
        $html = $this->newModule()->displayStatus(1, 0);
        $this->assertContains('btn-danger', $html);
        $this->assertContains('icon-remove', $html);
    }

    public function testActiveStatusUsesSuccessIcon()
    {
        $html = $this->newModule()->displayStatus(42, 1);
        $this->assertContains('btn-success', $html);
        $this->assertContains('icon-check', $html);
        $this->assertContains('changeStatus=1', $html);
        $this->assertContains('id_slide=42', $html);
        $this->assertContains('configure=ps_imageslider', $html);
    }

    public function testRequestValueOverridesStoredConfigForThatKeyOnly()
    {
        Configuration::updateValue('HOMESLIDER_SPEED', 5000);
        Configuration::updateValue('HOMESLIDER_PAUSE_ON_HOVER', 1);
        $_POST['HOMESLIDER_SPEED'] = '2500';
        $values = $this->newModule()->getConfigFieldsValues();
        $this->assertSame('2500', $values['HOMESLIDER_SPEED']);
        $this->assertSame('1', $values['HOMESLIDER_PAUSE_ON_HOVER']);
    }

    public function testAddFormFallsBackPerLanguageUntilOverridden()
    {
        $this->insertSlide(140, 1, 1, 1, 1, 'a.jpg');
        Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'homeslider_slides_lang` SET `title`="Stored" WHERE `id_homeslider_slides`=140 AND `id_lang`=1');
        Tools::submitKey('id_slide');
        $_POST['id_slide'] = '140';
        $_POST['title_2'] = 'RequestLang2';
        $fields = $this->newModule()->getAddFieldsValues();
        $this->assertSame('Stored', $fields['title'][1]);
        $this->assertSame('RequestLang2', $fields['title'][2]);
    }
}
