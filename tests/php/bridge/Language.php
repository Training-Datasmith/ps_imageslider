<?php

class Language
{
    public $id;
    public $iso_code;

    public function __construct($id = null)
    {
        if (null !== $id) {
            $this->id = (int) $id;
            foreach (self::getLanguages(false) as $lang) {
                if ((int) $lang['id_lang'] === (int) $id) {
                    $this->iso_code = $lang['iso_code'];
                    break;
                }
            }
        }
    }

    public static function getLanguages($active = true)
    {
        return array(
            array('id_lang' => 1, 'iso_code' => 'en', 'is_rtl' => 0),
            array('id_lang' => 2, 'iso_code' => 'ar', 'is_rtl' => 1),
        );
    }

    public static function getIDs($active = true)
    {
        $ids = array();
        foreach (self::getLanguages($active) as $lang) {
            $ids[] = (int) $lang['id_lang'];
        }

        return $ids;
    }
}
