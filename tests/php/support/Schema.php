<?php

class Schema
{
    public static function dropAll()
    {
        $db = Db::getInstance();
        $db->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'homeslider_slides_lang`');
        $db->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'homeslider_slides`');
        $db->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'homeslider`');
        $db->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'configuration`');
    }

    public static function createAll()
    {
        $db = Db::getInstance();
        $db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'configuration` (
                `id_configuration` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(254) NOT NULL,
                `value` text,
                `id_shop_group` int(11) DEFAULT NULL,
                `id_shop` int(11) DEFAULT NULL,
                PRIMARY KEY (`id_configuration`),
                KEY `name` (`name`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8
        ');
        $module = new Ps_ImageSlider();
        $ref = new ReflectionClass($module);
        $method = $ref->getMethod('createTables');
        $method->setAccessible(true);
        $method->invoke($module);
    }

    public static function resetConfiguration()
    {
        Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'configuration`');
        Configuration::updateValue('PS_LANG_DEFAULT', 1);
        Configuration::updateValue('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
    }
}
