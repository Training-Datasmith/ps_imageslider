<?php

class Shop
{
    const CONTEXT_SHOP = 1;
    const CONTEXT_GROUP = 2;
    const CONTEXT_ALL = 4;

    private static $context = self::CONTEXT_SHOP;
    private static $contextIdShop = 1;
    private static $contextIdShopGroup = 1;
    private static $featureActive = true;
    private static $shops = array(
        1 => array('id_shop' => 1, 'id_shop_group' => 7, 'name' => 'Shop One'),
        2 => array('id_shop' => 2, 'id_shop_group' => 7, 'name' => 'Shop Two'),
    );

    public $id;
    public $name;

    public function __construct($id = null)
    {
        if (null !== $id && isset(self::$shops[$id])) {
            $this->id = (int) $id;
            $this->name = self::$shops[$id]['name'];
        }
    }

    public function getBaseURL()
    {
        return 'https://shop.example/';
    }

    public static function resetState()
    {
        self::$context = self::CONTEXT_SHOP;
        self::$contextIdShop = 1;
        self::$contextIdShopGroup = 7;
        self::$featureActive = true;
    }

    public static function setFeatureActive($active)
    {
        self::$featureActive = (bool) $active;
    }

    public static function isFeatureActive()
    {
        return self::$featureActive;
    }

    public static function getContext()
    {
        return self::$context;
    }

    public static function setContext($type, $id = null)
    {
        self::$context = (int) $type;
        if ($type === self::CONTEXT_SHOP) {
            self::$contextIdShop = (int) $id;
            self::$contextIdShopGroup = self::getGroupFromShop($id, true);
        } elseif ($type === self::CONTEXT_GROUP) {
            self::$contextIdShop = null;
            self::$contextIdShopGroup = (int) $id;
        } else {
            self::$contextIdShop = null;
            self::$contextIdShopGroup = null;
        }
    }

    public static function getContextShopID($null = false)
    {
        return self::$contextIdShop;
    }

    public static function getContextShopGroupID()
    {
        return self::$contextIdShopGroup;
    }

    public static function getGroupFromShop($shopId, $asInt = false)
    {
        if (isset(self::$shops[$shopId])) {
            return (int) self::$shops[$shopId]['id_shop_group'];
        }

        return 0;
    }

    public static function getContextListShopID($share = false)
    {
        if (self::getContext() === self::CONTEXT_SHOP) {
            return array(self::getContextShopID());
        }
        if (self::getContext() === self::CONTEXT_GROUP) {
            $ids = array();
            foreach (self::$shops as $shop) {
                if ((int) $shop['id_shop_group'] === (int) self::$contextIdShopGroup) {
                    $ids[] = (int) $shop['id_shop'];
                }
            }

            return $ids;
        }

        return array_keys(self::$shops);
    }

    public static function getShops($active = true, $idGroup = null, $idsOnly = false)
    {
        $out = array();
        foreach (self::$shops as $shop) {
            if (null !== $idGroup && (int) $shop['id_shop_group'] !== (int) $idGroup) {
                continue;
            }
            $out[] = $idsOnly ? (int) $shop['id_shop'] : $shop;
        }

        return $out;
    }

    public static function getContextShopGroup()
    {
        return new ShopGroup(self::$contextIdShopGroup);
    }
}

class ShopGroup
{
    public $id;
    public $name;

    public function __construct($id)
    {
        $this->id = (int) $id;
        $this->name = 'Group ' . $this->id;
    }
}
