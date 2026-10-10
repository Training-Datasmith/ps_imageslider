<?php

class Context
{
    /** @var Context */
    private static $instance;

    public $shop;
    public $language;
    public $link;
    public $controller;
    public $smarty;

    public function __construct()
    {
        $this->shop = new Shop(Shop::getContextShopID());
        $this->language = new stdClass();
        $this->language->id = 1;
        $this->link = new Link();
        $this->controller = new TestFrontController();
        $this->smarty = new TestSmarty();
    }

    public static function getContext()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function reset()
    {
        self::$instance = null;
    }
}

class TestFrontController
{
    public $registeredAssets = array();

    public function registerStylesheet($id, $path, $options = array())
    {
        $this->registeredAssets[] = array('type' => 'css', 'id' => $id, 'path' => $path, 'options' => $options);
    }

    public function registerJavascript($id, $path, $options = array())
    {
        $this->registeredAssets[] = array('type' => 'js', 'id' => $id, 'path' => $path, 'options' => $options);
    }

    public function addJS($path)
    {
        $this->registeredAssets[] = array('type' => 'js-legacy', 'path' => $path);
    }

    public function getLanguages()
    {
        return Language::getLanguages(false);
    }
}

class TestSmarty
{
    public $assigned = array();

    public function assign($vars)
    {
        if (is_array($vars)) {
            $this->assigned = array_merge($this->assigned, $vars);
        }
    }
}
