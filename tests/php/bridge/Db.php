<?php

class Db
{
    /** @var Db */
    private static $instance;

    /** @var mysqli */
    private $link;

    /** @var callable|null */
    public static $executeSOverride;

    private function __construct()
    {
        $host = getenv('PS_DB_HOST') ? getenv('PS_DB_HOST') : '127.0.0.1';
        $user = getenv('PS_DB_USER') ? getenv('PS_DB_USER') : 'root';
        $pass = getenv('PS_DB_PASSWORD') ? getenv('PS_DB_PASSWORD') : 'root';
        $name = getenv('PS_DB_NAME') ? getenv('PS_DB_NAME') : 'imageslider';
        $this->link = new mysqli($host, $user, $pass, $name);
        if ($this->link->connect_error) {
            throw new RuntimeException('mysqli connect failed: ' . $this->link->connect_error);
        }
        $this->link->set_charset('utf8');
    }

    public static function getInstance($slave = false)
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function resetInstance()
    {
        if (self::$instance && self::$instance->link) {
            self::$instance->link->close();
        }
        self::$instance = null;
        self::$executeSOverride = null;
    }

    public function execute($sql)
    {
        return $this->link->query($sql) !== false;
    }

    public function getRow($sql)
    {
        $result = $this->executeS($sql);
        if (!is_array($result) || !isset($result[0])) {
            return false;
        }

        return $result[0];
    }

    public function executeS($sql)
    {
        if (self::$executeSOverride) {
            return call_user_func(self::$executeSOverride, $sql);
        }
        $res = $this->link->query($sql);
        if ($res === false) {
            return false;
        }
        if ($res === true) {
            return array();
        }
        $rows = array();
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        $res->free();

        return $rows;
    }

    public function insertId()
    {
        return (int) $this->link->insert_id;
    }

    public function escape($value)
    {
        return $this->link->real_escape_string($value);
    }
}
