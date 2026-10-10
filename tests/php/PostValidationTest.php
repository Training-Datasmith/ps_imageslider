<?php

class PostValidationTest extends PsImageSliderTestCase
{
    private function validSubmitSlide()
    {
        Tools::submitKey('submitSlide');
        $_POST['active_slide'] = '1';
        $_POST['has_picture'] = '1';
        $_POST['title_1'] = 'ok';
        $_POST['title_2'] = 'ok';
        $_POST['url_1'] = '';
        $_POST['url_2'] = '';
    }

    public function testSliderSpeedMustBeAnInteger()
    {
        Tools::submitKey('submitSlider');
        $_POST['HOMESLIDER_SPEED'] = '12abc';
        $this->assertFalse($this->newModule()->callPostValidation());
    }

    public function testSliderSpeedAcceptsAnIntegerString()
    {
        Tools::submitKey('submitSlider');
        $_POST['HOMESLIDER_SPEED'] = '5000';
        $this->assertTrue($this->newModule()->callPostValidation());
    }

    public function testChangeStatusRequiresAnIntegerId()
    {
        Tools::submitKey('changeStatus');
        $_POST['id_slide'] = 'nope';
        $this->assertFalse($this->newModule()->callPostValidation());
    }

    public function testActiveSlideMustBeZeroOrOne()
    {
        $this->validSubmitSlide();
        $_POST['active_slide'] = '2';
        $this->assertFalse($this->newModule()->callPostValidation());
    }

    public function testTitleLongerThan255IsRejected()
    {
        $this->validSubmitSlide();
        $_POST['title_1'] = str_repeat('a', 256);
        $this->assertFalse($this->newModule()->callPostValidation());
    }

    public function testLegendLongerThan255IsRejected()
    {
        $this->validSubmitSlide();
        $_POST['legend_1'] = str_repeat('a', 256);
        $this->assertFalse($this->newModule()->callPostValidation());
    }

    public function testUrlLongerThan255IsRejected()
    {
        $this->validSubmitSlide();
        $_POST['url_1'] = str_repeat('a', 256);
        $this->assertFalse($this->newModule()->callPostValidation());
    }

    public function testDescriptionLongerThan4000IsRejected()
    {
        $this->validSubmitSlide();
        $_POST['description_1'] = str_repeat('a', 4001);
        $this->assertFalse($this->newModule()->callPostValidation());
    }

    public function testUrlWithDisallowedCharactersIsRejected()
    {
        $this->validSubmitSlide();
        $_POST['url_1'] = 'https://example.com/a[';
        $this->assertFalse($this->newModule()->callPostValidation());
    }

    public function testEmptyUrlIsAccepted()
    {
        $this->validSubmitSlide();
        $this->assertTrue($this->newModule()->callPostValidation());
    }

    public function testImageFilenameWithDisallowedCharactersIsRejected()
    {
        $this->validSubmitSlide();
        $_POST['image_1'] = 'a/b.jpg';
        $this->assertFalse($this->newModule()->callPostValidation());
    }

    public function testOldImageFilenameWithDisallowedCharactersIsRejected()
    {
        $this->validSubmitSlide();
        $_POST['image_old_1'] = 'a/b.jpg';
        $this->assertFalse($this->newModule()->callPostValidation());
    }

    public function testMissingUploadIsRejectedWithoutHasPicture()
    {
        Tools::submitKey('submitSlide');
        $_POST['active_slide'] = '1';
        $_POST['title_1'] = 'ok';
        $this->assertFalse($this->newModule()->callPostValidation());
    }

    public function testDeleteOfUnknownOrNonIntegerSlideIsRejected()
    {
        Tools::submitKey('delete_id_slide');
        $_POST['delete_id_slide'] = '99999';
        $this->assertFalse($this->newModule()->callPostValidation());
        Tools::resetRequest();
        Tools::submitKey('delete_id_slide');
        $_POST['delete_id_slide'] = 'abc';
        $this->assertFalse($this->newModule()->callPostValidation());
    }

    public function testDeleteOfExistingSlidePasses()
    {
        $this->insertSlide(90, 1, 1, 1, 1, 'a.jpg');
        Tools::submitKey('delete_id_slide');
        $_POST['delete_id_slide'] = '90';
        $this->assertTrue($this->newModule()->callPostValidation());
    }

    public function testUnknownIntegerSlideIdIsRejected()
    {
        $this->validSubmitSlide();
        Tools::submitKey('id_slide');
        $_POST['id_slide'] = '99999';
        $this->assertFalse($this->newModule()->callPostValidation());
    }

    public function testKnownIntegerSlideIdPassesValidation()
    {
        $this->insertSlide(91, 1, 1, 1, 1, 'a.jpg');
        $this->validSubmitSlide();
        Tools::submitKey('id_slide');
        $_POST['id_slide'] = '91';
        $this->assertTrue($this->newModule()->callPostValidation());
    }
}
