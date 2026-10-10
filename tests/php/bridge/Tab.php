<?php

class Tab
{
    public static $storage = array();

    public $id;
    public $class_name;
    public $module;
    public $active;
    public $id_parent;
    public $name = array();

    public function __construct($id = null)
    {
        if (null !== $id && isset(self::$storage[$id])) {
            $this->id = (int) $id;
            foreach (self::$storage[$id] as $k => $v) {
                $this->$k = $v;
            }
        }
    }

    public function add()
    {
        $id = count(self::$storage) + 1;
        $this->id = $id;
        self::$storage[$id] = array(
            'class_name' => $this->class_name,
            'module' => $this->module,
            'active' => $this->active,
            'id_parent' => $this->id_parent,
            'name' => $this->name,
        );

        return true;
    }

    public function delete()
    {
        if ($this->id && isset(self::$storage[$this->id])) {
            unset(self::$storage[$this->id]);

            return true;
        }

        return false;
    }

    public static function getIdFromClassName($class)
    {
        foreach (self::$storage as $id => $tab) {
            if ($tab['class_name'] === $class) {
                return (int) $id;
            }
        }

        return 0;
    }

    public static function resetStorage()
    {
        self::$storage = array();
    }
}
