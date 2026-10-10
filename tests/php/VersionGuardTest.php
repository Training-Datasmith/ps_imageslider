<?php

class VersionGuardTest extends PsImageSliderTestCase
{
    public function testModuleExitsCleanlyWhenVersionConstantMissing()
    {
        $target = _PS_MODULE_DIR_ . 'ps_imageslider.php';
        $result = $this->runIncludeWrapper($target, false);
        $this->assertSame(0, $result['exit']);
        $this->assertFalse(file_exists($result['sentinel']));
        $this->assertNotContains('class Ps_ImageSlider', $result['stdout'] . $result['stderr']);
    }

    public function testModuleClassLoadsWhenVersionConstantDefined()
    {
        $sentinel = tempnam(sys_get_temp_dir(), 'modguard');
        if (file_exists($sentinel)) {
            unlink($sentinel);
        }
        $cmd = escapeshellarg(PHP_BINARY) . ' -d error_reporting=-1 ' . escapeshellarg(__DIR__ . '/module_guard_loader.php') . ' '
            . escapeshellarg($sentinel);
        $descriptors = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
        $proc = proc_open($cmd, $descriptors, $pipes);
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit = proc_close($proc);
        $this->assertNoChildDiagnostics($stdout, $stderr);
        $this->assertSame(0, $exit);
        $this->assertTrue(file_exists($sentinel));
        $this->assertContains('Ps_ImageSlider', file_get_contents($sentinel));
    }

    public function testUpgrade200ExitsWhenVersionConstantMissing()
    {
        $target = _PS_MODULE_DIR_ . 'upgrade/upgrade-from-homeslider.php';
        $result = $this->runIncludeWrapper($target, false);
        $this->assertSame(0, $result['exit']);
        $this->assertFalse(file_exists($result['sentinel']));
    }

    public function testUpgrade320ExitsWhenVersionConstantMissing()
    {
        $target = _PS_MODULE_DIR_ . 'upgrade/upgrade-3.2.0.php';
        $result = $this->runIncludeWrapper($target, false);
        $this->assertSame(0, $result['exit']);
        $this->assertFalse(file_exists($result['sentinel']));
    }

    public function testUpgrade200FunctionIsDefinedWhenVersionConstantDefined()
    {
        $target = _PS_MODULE_DIR_ . 'upgrade/upgrade-from-homeslider.php';
        $result = $this->runIncludeWrapper($target, true);
        $this->assertSame(0, $result['exit']);
        $this->assertTrue(file_exists($result['sentinel']));
    }

    public function testUpgrade320FunctionIsDefinedWhenVersionConstantDefined()
    {
        $target = _PS_MODULE_DIR_ . 'upgrade/upgrade-3.2.0.php';
        $result = $this->runIncludeWrapper($target, true);
        $this->assertSame(0, $result['exit']);
        $this->assertTrue(file_exists($result['sentinel']));
    }
}
