<?php

class SlidesQueryTest extends PsImageSliderTestCase
{
    public function testActiveShopLanguageAndImageFilters()
    {
        $this->insertSlide(1, 1, 1, 1, 1, 'a.jpg');
        $this->insertSlide(2, 2, 2, 1, 1, 'b.jpg');
        $this->insertSlide(3, 1, 3, 0, 1, 'c.jpg');
        $this->insertSlide(4, 1, 4, 1, 2, 'd.jpg');
        $this->insertSlide(5, 1, 5, 1, 1, '');
        $module = $this->newModule();
        $slides = $module->getSlides(true);
        $this->assertCount(1, $slides);
        $this->assertSame(1, (int) $slides[0]['id_slide']);
    }

    public function testForceShowAllKeepsSlidesWithEmptyImage()
    {
        $this->insertSlide(10, 1, 1, 1, 1, '');
        $module = $this->newModule();
        $this->assertCount(0, $module->getSlides(true));
        $this->assertCount(1, $module->getSlides(null, true));
    }

    public function testResultsAreOrderedByPosition()
    {
        $this->insertSlide(11, 1, 3, 1, 1, 'a.jpg');
        $this->insertSlide(12, 1, 1, 1, 1, 'b.jpg');
        $this->insertSlide(13, 1, 2, 1, 1, 'c.jpg');
        $slides = $this->newModule()->getSlides(null, true);
        $ids = array();
        foreach ($slides as $slide) {
            $ids[] = (int) $slide['id_slide'];
        }
        $this->assertSame(array(12, 13, 11), $ids);
    }

    public function testUrlsAreNormalized()
    {
        $this->insertSlide(20, 1, 1, 1, 1, 'a.jpg', '#anchor');
        $this->insertSlide(21, 1, 2, 1, 1, 'b.jpg', 'https://ex.com/x');
        $this->insertSlide(22, 1, 3, 1, 1, 'c.jpg', 'prestashop.com/p');
        $this->insertSlide(23, 1, 4, 1, 1, 'd.jpg', 'index.php?id=1');
        $module = $this->newModule();
        $byId = array();
        foreach ($module->getSlides(null, true) as $slide) {
            $byId[(int) $slide['id_slide']] = $slide['url'];
        }
        $this->assertSame('#anchor', $byId[20]);
        $this->assertSame('https://ex.com/x', $byId[21]);
        $this->assertSame('//prestashop.com/p', $byId[22]);
        $this->assertSame('https://shop.example/index.php?id=1', $byId[23]);
    }

    public function testInactiveFilterIsNotAppliedWhenActiveFlagIsFalse()
    {
        $this->insertSlide(30, 1, 1, 0, 1, 'a.jpg');
        $module = $this->newModule();
        $this->assertCount(1, $module->getSlides(false));
        $this->assertCount(1, $module->getSlides(null));
    }

    public function testGetAllImagesMapsLanguageAndShop()
    {
        $db = Db::getInstance();
        $this->insertSlide(40, 1, 1, 1, 1, 'en.jpg');
        $db->execute('REPLACE INTO `' . _DB_PREFIX_ . 'homeslider_slides_lang`
            (`id_homeslider_slides`, `id_lang`, `title`, `description`, `legend`, `url`, `image`)
            VALUES (40, 2, "t", "d", "l", "", "ar.jpg")');
        $images = $this->newModule()->getAllImagesBySlidesId(40, null, 1);
        $this->assertSame(array(1 => 'en.jpg', 2 => 'ar.jpg'), $images);
    }

    public function testGetAllImagesHonorsActiveFlag()
    {
        $this->insertSlide(41, 1, 1, 0, 1, 'off.jpg');
        $module = $this->newModule();
        $this->assertSame(array(), $module->getAllImagesBySlidesId(41, true));
        $this->assertSame(array(1 => 'off.jpg'), $module->getAllImagesBySlidesId(41));
    }

    public function testSlideExistsDistinguishesRowFromFalse()
    {
        $this->insertSlide(50, 1, 1, 1, 1, 'a.jpg');
        $module = $this->newModule();
        $row = $module->slideExists(50);
        $this->assertTrue(is_array($row));
        $this->assertSame(50, (int) $row['id_slide']);
        $this->assertFalse($module->slideExists(99999));
    }

    public function testAssociatedShopIdsAreIntsAndFalseWhenQueryFails()
    {
        $db = Db::getInstance();
        $this->insertSlide(60, 1, 1, 1, 1, 'a.jpg');
        $db->execute('INSERT INTO `' . _DB_PREFIX_ . 'homeslider` (`id_homeslider_slides`, `id_shop`) VALUES (60, 2)');
        $ids = Ps_HomeSlide::getAssociatedIdsShop(60);
        $this->assertSame(array(1, 2), $ids);
        $this->assertSame('integer', gettype($ids[0]));
        Db::$executeSOverride = function () {
            return false;
        };
        $this->assertFalse(Ps_HomeSlide::getAssociatedIdsShop(60));
    }

    public function testNextPositionIsOnePastMax()
    {
        $this->insertSlide(70, 1, 4, 1, 1, 'a.jpg');
        $this->assertSame(5, (int) $this->newModule()->getNextPosition());
    }

    public function testNextPositionIsOneWhenTableIsEmpty()
    {
        $this->assertSame(1, (int) $this->newModule()->getNextPosition());
    }
}
