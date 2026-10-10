<?php

class ShopDuplicationTest extends PsImageSliderTestCase
{
    public function testCopiesAssociationsOntoTheNewShop()
    {
        $this->insertSlide(120, 1, 1, 1, 1, 'a.jpg');
        $module = $this->newModule();
        $module->hookActionShopDataDuplication(array('old_id_shop' => 1, 'new_id_shop' => 2));
        $rows = Db::getInstance()->executeS('SELECT `id_shop` FROM `' . _DB_PREFIX_ . 'homeslider` WHERE `id_homeslider_slides`=120 ORDER BY `id_shop`');
        $shops = array();
        foreach ($rows as $row) {
            $shops[] = (int) $row['id_shop'];
        }
        $this->assertSame(array(1, 2), $shops);
        $this->assertSame('module:ps_imageslider/views/templates/hook/slider.tpl', $module->lastClearedTemplate);
    }

    public function testDuplicateHookIsIdempotentForSameShopPair()
    {
        $this->insertSlide(121, 1, 1, 1, 1, 'a.jpg');
        $module = $this->newModule();
        $params = array('old_id_shop' => 1, 'new_id_shop' => 2);
        $module->hookActionShopDataDuplication($params);
        $module->hookActionShopDataDuplication($params);
        $rows = Db::getInstance()->executeS('SELECT `id_shop` FROM `' . _DB_PREFIX_ . 'homeslider` WHERE `id_homeslider_slides`=121 AND `id_shop`=2');
        $this->assertCount(1, $rows);
    }
}
