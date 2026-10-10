<?php

class ShopMessagesTest extends PsImageSliderTestCase
{
    public function testMultishopWarningOnlyForGroupAndAllContexts()
    {
        $module = $this->newModule();
        Shop::setContext(Shop::CONTEXT_GROUP, 7);
        $this->assertContains('alert-warning', $module->callGetWarningMultishopHtml());
        Shop::setContext(Shop::CONTEXT_ALL);
        $this->assertContains('alert-warning', $module->callGetWarningMultishopHtml());
        Shop::setContext(Shop::CONTEXT_SHOP, 1);
        $this->assertSame('', $module->callGetWarningMultishopHtml());
    }

    public function testShopInfoHiddenWhenMultishopIsOff()
    {
        Shop::setFeatureActive(false);
        $this->assertSame('', $this->newModule()->callGetCurrentShopInfoMsg());
    }

    public function testShopInfoIncludesTheActiveShopOrGroupName()
    {
        Shop::setFeatureActive(true);
        Shop::setContext(Shop::CONTEXT_SHOP, 1);
        $html = $this->newModule()->callGetCurrentShopInfoMsg();
        $this->assertContains('alert-info', $html);
        $this->assertContains('Shop One', $html);
        Shop::setContext(Shop::CONTEXT_GROUP, 7);
        $htmlGroup = $this->newModule()->callGetCurrentShopInfoMsg();
        $this->assertContains('Group 7', $htmlGroup);
    }

    public function testEditContextErrorIncludesTheShopName()
    {
        $html = $this->newModule()->callGetShopContextError('Shop One', 'edit');
        $this->assertContains('alert-danger', $html);
        $this->assertContains('Shop One', $html);
    }

    public function testAddContextErrorDoesNotEchoTheShopList()
    {
        $html = $this->newModule()->callGetShopContextError('Shop Secret', 'add');
        $this->assertContains('alert-danger', $html);
        $this->assertNotContains('Shop Secret', $html);
    }

    public function testAssociationErrorIncludesTheNumericSlideId()
    {
        $html = $this->newModule()->callGetShopAssociationError(77);
        $this->assertContains('alert-danger', $html);
        $this->assertContains('77', $html);
    }
}
