<?php

class ImageManager
{
    public static function validateUpload($file)
    {
        return false;
    }

    public static function resize($src, $dest, $width = null, $height = null, $type = null)
    {
        return copy($src, $dest);
    }
}
