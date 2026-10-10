<?php

class Module
{
    public $name;
    public $tab;
    public $version;
    public $author;
    public $need_instance;
    public $bootstrap;
    public $displayName;
    public $description;
    public $ps_versions_compliancy;
    public $context;
    public $smarty;
    public $table = 'module';
    public $identifier = 'id_module';
    public $_path = '/modules/ps_imageslider/';
    public $registeredHooks = array();
    public $cacheHit = false;
    public $lastClearedTemplate;
    public $lastFetchedTemplate;
    public $lastAssignedVars;
    public $parentInstallResult = true;
    public $parentUninstallResult = true;
    public $configurationUpdateResults = array();

    public function __construct()
    {
        $this->context = Context::getContext();
        $this->smarty = $this->context->smarty;
    }

    public function install()
    {
        return $this->parentInstallResult;
    }

    public function uninstall()
    {
        return $this->parentUninstallResult;
    }

    public function registerHook($name)
    {
        $this->registeredHooks[] = $name;

        return true;
    }

    public function unregisterHook($name)
    {
        $this->registeredHooks = array_values(array_diff($this->registeredHooks, array($name)));

        return true;
    }

    public function trans($string, $params = array(), $domain = null)
    {
        if (!empty($params)) {
            array_unshift($params, $string);

            return call_user_func_array('sprintf', $params);
        }

        return $string;
    }

    public function getTranslator()
    {
        return $this;
    }

    public function displayError($message)
    {
        return '<div class="error">' . $message . '</div>';
    }

    public function displayConfirmation($message)
    {
        return '<div class="confirmation">' . $message . '</div>';
    }

    public function display($file, $template)
    {
        return '<!-- tpl:' . $template . ' -->';
    }

    public function fetch($template, $cacheId = null)
    {
        $this->lastFetchedTemplate = $template;
        $pause = '';
        if (isset($this->smarty->assigned['homeslider']['pause'])) {
            $pause = $this->smarty->assigned['homeslider']['pause'];
        }

        return 'WIDGET:' . ($pause === '' ? 'none' : $pause);
    }

    public function isCached($template, $cacheId)
    {
        return $this->cacheHit;
    }

    protected function _clearCache($template)
    {
        $this->lastClearedTemplate = $template;
    }

    public function getCacheId()
    {
        return 'homeslider_cache';
    }
}
