<?php

class ModuleAdminController
{
    public $module;
    public $lastAjaxOutput;

    public function ajaxRender($payload)
    {
        $this->lastAjaxOutput = $payload;
        echo $payload;
    }
}
