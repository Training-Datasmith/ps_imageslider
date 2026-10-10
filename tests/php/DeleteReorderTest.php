<?php

class DeleteReorderTest extends PsImageSliderTestCase
{
    public function testDeleteUnlinksNonSampleFileFromModuleImagesDir()
    {
        $path = _PS_MODULE_DIR_ . 'images/zz-test-slide.jpg';
        file_put_contents($path, 'x');
        $slide = new Ps_HomeSlide();
        $slide->active = 1;
        $slide->position = 1;
        $slide->image = array(1 => 'zz-test-slide.jpg');
        $slide->add();
        $slide->delete();
        $this->assertFileNotExists($path);
    }

    public function testDeleteDoesNotUnlinkSampleFiles()
    {
        $samplePath = _PS_MODULE_DIR_ . 'images/sample-1.jpg';
        $this->assertFileExists($samplePath);
        $slide = new Ps_HomeSlide();
        $slide->active = 1;
        $slide->position = 1;
        $slide->image = array(1 => 'sample-1.jpg');
        $slide->add();
        $slide->delete();
        $this->assertFileExists($samplePath);
    }

    public function testDeletingSlideIdOneCompactsHigherPositions()
    {
        $this->insertSlide(1, 1, 1, 1, 1, 'a.jpg');
        $this->insertSlide(2, 1, 2, 1, 1, 'b.jpg');
        $slide = new Ps_HomeSlide(1);
        $slide->delete();
        $row = Db::getInstance()->getRow('SELECT `position` FROM `' . _DB_PREFIX_ . 'homeslider_slides` WHERE `id_homeslider_slides`=2');
        $this->assertSame('1', $row['position']);
    }

    public function testDeletingAMiddleSlideCompactsOnlyLaterPositions()
    {
        $this->insertSlide(201, 1, 1, 1, 1, 'a.jpg');
        $this->insertSlide(202, 1, 2, 1, 1, 'b.jpg');
        $this->insertSlide(203, 1, 3, 1, 1, 'c.jpg');
        $slide = new Ps_HomeSlide(202);
        $slide->delete();
        $p1 = Db::getInstance()->getRow('SELECT `position` FROM `' . _DB_PREFIX_ . 'homeslider_slides` WHERE `id_homeslider_slides`=201');
        $p3 = Db::getInstance()->getRow('SELECT `position` FROM `' . _DB_PREFIX_ . 'homeslider_slides` WHERE `id_homeslider_slides`=203');
        $this->assertSame('1', $p1['position']);
        $this->assertSame('2', $p3['position']);
    }

    public function testDeletingTheLastSlideLeavesEarlierPositions()
    {
        $this->insertSlide(211, 1, 1, 1, 1, 'a.jpg');
        $this->insertSlide(212, 1, 2, 1, 1, 'b.jpg');
        $slide = new Ps_HomeSlide(212);
        $slide->delete();
        $p1 = Db::getInstance()->getRow('SELECT `position` FROM `' . _DB_PREFIX_ . 'homeslider_slides` WHERE `id_homeslider_slides`=211');
        $this->assertSame('1', $p1['position']);
    }

    public function testAddWritesTheShopAssociation()
    {
        $slide = new Ps_HomeSlide();
        $slide->active = 1;
        $slide->position = 1;
        $slide->title = array(1 => 't');
        $slide->description = array(1 => 'd');
        $slide->legend = array(1 => 'l');
        $slide->url = array(1 => '');
        $slide->image = array(1 => 'sample-1.jpg');
        $this->assertTrue($slide->add());
        $row = Db::getInstance()->getRow('SELECT `id_shop` FROM `' . _DB_PREFIX_ . 'homeslider` WHERE `id_homeslider_slides`=' . (int) $slide->id);
        $this->assertSame('1', $row['id_shop']);
    }
}
