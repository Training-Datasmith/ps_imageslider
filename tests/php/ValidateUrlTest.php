<?php

class ValidateUrlTest extends PsImageSliderTestCase
{
    public function testEmptyStringIsUnchanged()
    {
        $this->assertSame('', $this->newModule()->callValidateUrl(''));
    }

    public function testAnchorIsUnchanged()
    {
        $this->assertSame('#slide', $this->newModule()->callValidateUrl('#slide'));
    }

    public function testHttpUrlIsUnchanged()
    {
        $url = 'http://example.com/a';
        $this->assertSame($url, $this->newModule()->callValidateUrl($url));
    }

    public function testHttpsUrlIsUnchanged()
    {
        $url = 'https://example.com/a';
        $this->assertSame($url, $this->newModule()->callValidateUrl($url));
    }

    public function testProtocolRelativeUrlIsUnchanged()
    {
        $url = '//cdn.example.com/a.jpg';
        $this->assertSame($url, $this->newModule()->callValidateUrl($url));
    }

    public function testBareDomainGetsProtocolRelativePrefix()
    {
        $this->assertSame('//prestashop.com/path', $this->newModule()->callValidateUrl('prestashop.com/path'));
    }

    public function testIndexPhpIsPrefixedWithShopBase()
    {
        $this->assertSame(
            'https://shop.example/index.php?id_product=1',
            $this->newModule()->callValidateUrl('index.php?id_product=1')
        );
    }

    public function testRelativePathIsPrefixedWithShopBase()
    {
        $this->assertSame('https://shop.example/cms/1', $this->newModule()->callValidateUrl('cms/1'));
    }

    public function testLeadingSlashIsNotDoubled()
    {
        $this->assertSame('https://shop.example/cms/1', $this->newModule()->callValidateUrl('/cms/1'));
    }
}
