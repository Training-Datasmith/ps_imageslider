<?php

class Validate
{
    public static function isCleanHtml($html, $allow_iframe = false)
    {
        $events = 'onmousedown|onmousemove|onmmouseup|onmouseover|onmouseout|onload|onunload|onfocus|onblur|onchange';
        $events .= '|onsubmit|ondblclick|onclick|onkeydown|onkeyup|onkeypress|onmouseenter|onmouseleave|onerror|onselect|onreset|onabort|ondragdrop|onresize|onactivate|onafterprint|onmoveend';
        $events .= '|onafterupdate|onbeforeactivate|onbeforecopy|onbeforecut|onbeforedeactivate|onbeforeeditfocus|onbeforepaste|onbeforeprint|onbeforeunload|onbeforeupdate|onmove';
        $events .= '|onbounce|oncellchange|oncontextmenu|oncontrolselect|oncopy|oncut|ondataavailable|ondatasetchanged|ondatasetcomplete|ondeactivate|ondrag|ondragend|ondragenter|onmousewheel';
        $events .= '|ondragleave|ondragover|ondragstart|ondrop|onerrorupdate|onfilterchange|onfinish|onfocusin|onfocusout|onhashchange|onhelp|oninput|onlosecapture|onmessage|onmouseup|onmovestart';
        $events .= '|onoffline|ononline|onpaste|onpropertychange|onreadystatechange|onresizeend|onresizestart|onrowenter|onrowexit|onrowsdelete|onrowsinserted|onscroll|onsearch|onselectionchange';
        $events .= '|onselectstart|onstart|onstop|onanimationcancel|onanimationend|onanimationiteration|onanimationstart';

        if (preg_match('/<[\s]*script/ims', $html) || preg_match('/(' . $events . ')[\s]*=/ims', $html) || preg_match('/.*script\:/ims', $html)) {
            return false;
        }

        if (!$allow_iframe && preg_match('/<[\s]*(i?frame|form|input|embed|object)/ims', $html)) {
            return false;
        }

        return true;
    }

    public static function isBool($bool)
    {
        return $bool === null || is_bool($bool) || preg_match('/^(0|1)$/', $bool);
    }

    public static function isInt($value)
    {
        return (string) (int) $value === (string) $value || $value === false;
    }

    public static function isUnsignedInt($value)
    {
        return (is_numeric($value) || is_string($value))
            && (string) (int) $value === (string) $value
            && $value < 4294967296
            && $value >= 0;
    }

    public static function isUrl($url)
    {
        return preg_match('/^[~:#,$%&_=\(\)\.\? \+\-@\/a-zA-Z0-9\pL\pS-]+$/u', $url);
    }

    public static function isFileName($name)
    {
        return preg_match('/^[a-zA-Z0-9_.-]+$/', $name);
    }

    public static function isLoadedObject($object)
    {
        return is_object($object) && isset($object->id) && (int) $object->id > 0;
    }
}
