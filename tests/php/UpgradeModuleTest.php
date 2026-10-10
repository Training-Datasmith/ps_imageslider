<?php

class UpgradeSpyModule extends Ps_ImageSlider
{
    public $hookLog = array();
    public $unregisterResult = true;
    public $registerResult = true;

    public function unregisterHook($name)
    {
        $this->hookLog[] = 'unreg:' . $name;

        return $this->unregisterResult;
    }

    public function registerHook($name)
    {
        $this->hookLog[] = 'reg:' . $name;

        return $this->registerResult;
    }
}

class UpgradeModuleTest extends PsImageSliderTestCase
{
    public function testUpgrade200RequestsTheHookAndLegacyKeyChanges()
    {
        require_once _PS_MODULE_DIR_ . 'upgrade/upgrade-from-homeslider.php';
        Configuration::updateValue('HOMESLIDER_PAUSE', 1);
        Configuration::updateValue('HOMESLIDER_LOOP', 1);
        $module = new UpgradeSpyModule();
        $this->assertTrue(upgrade_module_2_0_0($module));
        $this->assertContains('unreg:displayTopColumn', $module->hookLog);
        $this->assertContains('reg:displayHome', $module->hookLog);
        $this->assertFalse(Configuration::get('HOMESLIDER_PAUSE'));
        $this->assertFalse(Configuration::get('HOMESLIDER_LOOP'));
    }

    public function testUpgrade200IsFalseWhenAnyCollaboratorIsFalse()
    {
        require_once _PS_MODULE_DIR_ . 'upgrade/upgrade-from-homeslider.php';
        Configuration::updateValue('HOMESLIDER_PAUSE', 1);
        Configuration::updateValue('HOMESLIDER_LOOP', 1);
        $module = new UpgradeSpyModule();
        $module->unregisterResult = false;
        $this->assertFalse(upgrade_module_2_0_0($module));

        Configuration::updateValue('HOMESLIDER_PAUSE', 1);
        Configuration::updateValue('HOMESLIDER_LOOP', 1);
        $module = new UpgradeSpyModule();
        $module->registerResult = false;
        $this->assertFalse(upgrade_module_2_0_0($module));

        Configuration::updateValue('HOMESLIDER_PAUSE', 1);
        Configuration::updateValue('HOMESLIDER_LOOP', 1);
        Configuration::$failDeleteByName = 'HOMESLIDER_PAUSE';
        $this->assertFalse(upgrade_module_2_0_0(new UpgradeSpyModule()));
        Configuration::resetHooks();

        Configuration::updateValue('HOMESLIDER_PAUSE', 1);
        Configuration::updateValue('HOMESLIDER_LOOP', 1);
        Configuration::$failDeleteByName = 'HOMESLIDER_LOOP';
        $this->assertFalse(upgrade_module_2_0_0(new UpgradeSpyModule()));
        Configuration::resetHooks();
    }

    public function testUpgrade320CreatesTheConfigureSlidesTab()
    {
        require_once _PS_MODULE_DIR_ . 'upgrade/upgrade-3.2.0.php';
        $module = new UpgradeSpyModule();
        $this->assertTrue(upgrade_module_3_2_0($module));
        $this->assertGreaterThan(0, Tab::getIdFromClassName('AdminConfigureSlides'));
    }
}
