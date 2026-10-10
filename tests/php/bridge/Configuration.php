<?php

class Configuration
{
    public static $failOnKey = null;
    public static $failDeleteByName = null;

    public static function resetHooks()
    {
        self::$failOnKey = null;
        self::$failDeleteByName = null;
    }

    public static function updateValue($key, $value, $html = false, $idShopGroup = null, $idShop = null)
    {
        if (self::$failOnKey === $key) {
            return false;
        }
        $db = Db::getInstance();
        $where = '`name` = "' . $db->escape($key) . '"';
        if (null !== $idShopGroup) {
            $where .= ' AND `id_shop_group` = ' . (int) $idShopGroup;
        } else {
            $where .= ' AND `id_shop_group` IS NULL';
        }
        if (null !== $idShop) {
            $where .= ' AND `id_shop` = ' . (int) $idShop;
        } else {
            $where .= ' AND `id_shop` IS NULL';
        }
        $db->execute('DELETE FROM `' . _DB_PREFIX_ . 'configuration` WHERE ' . $where);
        $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'configuration` (`name`, `value`, `id_shop_group`, `id_shop`)
            VALUES ("' . $db->escape($key) . '", "' . $db->escape((string) $value) . '", '
            . (null === $idShopGroup ? 'NULL' : (int) $idShopGroup) . ', '
            . (null === $idShop ? 'NULL' : (int) $idShop) . ')';

        return $db->execute($sql);
    }

    public static function get($key, $idLang = null, $idShopGroup = null, $idShop = null)
    {
        $db = Db::getInstance();
        $where = '`name` = "' . $db->escape($key) . '"';
        if (null !== $idShopGroup) {
            $where .= ' AND `id_shop_group` = ' . (int) $idShopGroup;
        } else {
            $where .= ' AND `id_shop_group` IS NULL';
        }
        if (null !== $idShop) {
            $where .= ' AND `id_shop` = ' . (int) $idShop;
        } else {
            $where .= ' AND `id_shop` IS NULL';
        }
        $row = $db->getRow('SELECT `value` FROM `' . _DB_PREFIX_ . 'configuration` WHERE ' . $where . ' LIMIT 1');
        if (!$row) {
            $row = $db->getRow('SELECT `value` FROM `' . _DB_PREFIX_ . 'configuration` WHERE `name` = "' . $db->escape($key) . '" AND `id_shop_group` IS NULL AND `id_shop` IS NULL LIMIT 1');
        }
        if (!$row) {
            return false;
        }

        return $row['value'];
    }

    public static function deleteByName($key)
    {
        if (self::$failDeleteByName === $key) {
            return false;
        }

        return Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'configuration` WHERE `name` = "' . Db::getInstance()->escape($key) . '"');
    }
}
