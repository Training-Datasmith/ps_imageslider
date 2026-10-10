<?php

class PsHomeSlideDefinitionTest extends PsImageSliderTestCase
{
    public function testTablePrimaryKeyAndMultilang()
    {
        $def = Ps_HomeSlide::$definition;
        $this->assertSame('homeslider_slides', $def['table']);
        $this->assertSame('id_homeslider_slides', $def['primary']);
        $this->assertTrue($def['multilang']);
    }

    public function testFieldTypesSizesAndValidators()
    {
        $fields = Ps_HomeSlide::$definition['fields'];
        $this->assertSame(ObjectModel::TYPE_BOOL, $fields['active']['type']);
        $this->assertTrue($fields['active']['required']);
        $this->assertSame('isbool', strtolower($fields['active']['validate']));
        $this->assertSame(ObjectModel::TYPE_INT, $fields['position']['type']);
        $this->assertSame('isunsignedint', strtolower($fields['position']['validate']));
        $this->assertSame(4000, $fields['description']['size']);
        $this->assertSame('iscleanhtml', strtolower($fields['description']['validate']));
        $this->assertSame(255, $fields['title']['size']);
        $this->assertSame(255, $fields['url']['size']);
        $this->assertSame('isurl', strtolower($fields['url']['validate']));
        $this->assertTrue($fields['title']['lang']);
        $this->assertSame(ObjectModel::TYPE_STRING, $fields['image']['type']);
        $this->assertTrue($fields['image']['lang']);
        $this->assertSame(255, $fields['image']['size']);
    }
}
