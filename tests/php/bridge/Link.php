<?php

class Link
{
    public function getBaseLink()
    {
        return 'https://shop.example/';
    }

    public function getMediaLink($path)
    {
        return 'https://shop.example/media' . $path;
    }

    public function getAdminLink($controller, $ssl = true, $params = array(), $params2 = array())
    {
        $merged = array_merge($params2, $params);
        $query = http_build_query($merged);
        $base = 'https://shop.example/admin/' . $controller;
        if ($query !== '') {
            return $base . '?' . $query;
        }

        return $base;
    }
}
