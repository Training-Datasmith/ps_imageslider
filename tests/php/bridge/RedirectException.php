<?php

class RedirectException extends Exception
{
    public $url;

    public function __construct($url)
    {
        parent::__construct('Redirect: ' . $url);
        $this->url = $url;
    }
}
