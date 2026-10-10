<?php

class AdminConfigureSlidesControllerTest extends PsImageSliderTestCase
{
    private function runAjax($action, $slides)
    {
        $marker = tempnam(sys_get_temp_dir(), 'cachemarker');
        if (file_exists($marker)) {
            unlink($marker);
        }
        putenv('PS_TEST_CACHE_MARKER=' . $marker);
        if ($slides === null) {
            $slidesArg = '';
        } elseif (is_array($slides) && count($slides) === 0) {
            $slidesArg = '__EMPTY__';
        } else {
            $slidesArg = implode(',', $slides);
        }
        $cmd = escapeshellarg(PHP_BINARY) . ' -d error_reporting=-1 ' . escapeshellarg(__DIR__ . '/admin_ajax_runner.php') . ' '
            . escapeshellarg($action) . ' ' . escapeshellarg($slidesArg);
        $descriptors = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
        $proc = proc_open($cmd, $descriptors, $pipes);
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exit = proc_close($proc);
        $this->assertNoChildDiagnostics($stdout, $stderr);
        $markerContents = file_exists($marker) ? file_get_contents($marker) : '';

        return array('exit' => $exit, 'stdout' => $stdout, 'stderr' => $stderr, 'marker' => $markerContents);
    }

    public function testMissingActionLeavesPositionsAndReturnsError()
    {
        $this->insertSlide(130, 1, 1, 1, 1, 'a.jpg');
        $this->insertSlide(131, 1, 2, 1, 1, 'b.jpg');
        foreach (array('', 'nope') as $action) {
            $result = $this->runAjax($action, array(130, 131));
            $decoded = json_decode($result['stdout'], true);
            $this->assertTrue($decoded['error']);
            $row = Db::getInstance()->getRow('SELECT `position` FROM `' . _DB_PREFIX_ . 'homeslider_slides` WHERE `id_homeslider_slides`=131');
            $this->assertSame('2', $row['position']);
            $this->assertSame('', $result['marker']);
        }
    }

    public function testEmptySlidesListLeavesPositionsAndReturnsError()
    {
        $this->insertSlide(132, 1, 1, 1, 1, 'a.jpg');
        $result = $this->runAjax('updateSlidesPosition', array());
        $decoded = json_decode($result['stdout'], true);
        $this->assertTrue($decoded['error']);
        $row = Db::getInstance()->getRow('SELECT `position` FROM `' . _DB_PREFIX_ . 'homeslider_slides` WHERE `id_homeslider_slides`=132');
        $this->assertSame('1', $row['position']);
    }

    public function testSubmittedOrderIsStoredAndCacheIsCleared()
    {
        $this->insertSlide(133, 1, 1, 1, 1, 'a.jpg');
        $this->insertSlide(134, 1, 2, 1, 1, 'b.jpg');
        $this->insertSlide(135, 1, 3, 1, 1, 'c.jpg');
        $result = $this->runAjax('updateSlidesPosition', array(135, 133, 134));
        $decoded = json_decode($result['stdout'], true);
        $this->assertTrue($decoded['success']);
        $rows = Db::getInstance()->executeS('SELECT `id_homeslider_slides`, `position` FROM `' . _DB_PREFIX_ . 'homeslider_slides` WHERE `id_homeslider_slides` IN (133,134,135) ORDER BY `position`');
        $ids = array();
        $positions = array();
        foreach ($rows as $row) {
            $ids[] = (int) $row['id_homeslider_slides'];
            $positions[] = (int) $row['position'];
        }
        $this->assertSame(array(135, 133, 134), $ids);
        $this->assertSame(count(array_unique($positions)), count($positions));
        $this->assertContains('module:ps_imageslider/views/templates/hook/slider.tpl', $result['marker']);
    }
}
