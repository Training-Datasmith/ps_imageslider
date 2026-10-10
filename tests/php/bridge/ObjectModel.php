<?php

class ObjectModel
{
    const TYPE_BOOL = 1;
    const TYPE_INT = 2;
    const TYPE_STRING = 3;
    const TYPE_HTML = 4;

    public $id;
    public $force_id = false;

    public function __construct($id = null, $id_lang = null)
    {
        if ($id) {
            $this->id = (int) $id;
            $this->hydrate($id, $id_lang);
        }
    }

    protected function hydrate($id, $id_lang = null)
    {
        $def = static::$definition;
        $table = _DB_PREFIX_ . $def['table'];
        $primary = $def['primary'];
        $row = Db::getInstance()->getRow('SELECT * FROM `' . $table . '` WHERE `' . $primary . '` = ' . (int) $id);
        if (!$row) {
            $this->id = null;

            return;
        }
        foreach ($row as $key => $value) {
            if ($key !== $primary) {
                $this->$key = $value;
            }
        }
        if (!empty($def['multilang'])) {
            $langTable = $table . '_lang';
            $rows = Db::getInstance()->executeS(
                'SELECT * FROM `' . $langTable . '` WHERE `' . $primary . '` = ' . (int) $id
            );
            foreach ($def['fields'] as $field => $meta) {
                if (!empty($meta['lang'])) {
                    $this->$field = array();
                    if (is_array($rows)) {
                        foreach ($rows as $langRow) {
                            $this->{$field}[(int) $langRow['id_lang']] = $langRow[$field];
                        }
                    }
                }
            }
        }
    }

    public function add($autodate = true, $null_values = false)
    {
        $def = static::$definition;
        $table = _DB_PREFIX_ . $def['table'];
        $primary = $def['primary'];
        $fields = array();
        foreach ($def['fields'] as $name => $meta) {
            if (empty($meta['lang'])) {
                $fields[] = '`' . $name . '` = "' . Db::getInstance()->escape($this->$name) . '"';
            }
        }
        $sql = 'INSERT INTO `' . $table . '` SET ' . implode(', ', $fields);
        if (!Db::getInstance()->execute($sql)) {
            return false;
        }
        $this->id = (int) Db::getInstance()->insertId();
        $this->saveLang();

        return true;
    }

    public function update($null_values = false)
    {
        $def = static::$definition;
        $table = _DB_PREFIX_ . $def['table'];
        $primary = $def['primary'];
        $sets = array();
        foreach ($def['fields'] as $name => $meta) {
            if (empty($meta['lang'])) {
                $sets[] = '`' . $name . '` = "' . Db::getInstance()->escape($this->$name) . '"';
            }
        }
        $sql = 'UPDATE `' . $table . '` SET ' . implode(', ', $sets) . ' WHERE `' . $primary . '` = ' . (int) $this->id;
        if (!Db::getInstance()->execute($sql)) {
            return false;
        }
        Db::getInstance()->execute('DELETE FROM `' . $table . '_lang` WHERE `' . $primary . '` = ' . (int) $this->id);
        $this->saveLang();

        return true;
    }

    public function delete()
    {
        $def = static::$definition;
        $table = _DB_PREFIX_ . $def['table'];
        $primary = $def['primary'];
        Db::getInstance()->execute('DELETE FROM `' . $table . '_lang` WHERE `' . $primary . '` = ' . (int) $this->id);
        Db::getInstance()->execute('DELETE FROM `' . $table . '` WHERE `' . $primary . '` = ' . (int) $this->id);
        $this->id = null;

        return true;
    }

    protected function saveLang()
    {
        $def = static::$definition;
        if (empty($def['multilang'])) {
            return;
        }
        $table = _DB_PREFIX_ . $def['table'] . '_lang';
        $primary = $def['primary'];
        foreach (Language::getLanguages(false) as $lang) {
            $idLang = (int) $lang['id_lang'];
            $cols = array('`' . $primary . '`', '`id_lang`');
            $vals = array((int) $this->id, $idLang);
            foreach ($def['fields'] as $name => $meta) {
                if (!empty($meta['lang'])) {
                    $cols[] = '`' . $name . '`';
                    $value = isset($this->{$name}[$idLang]) ? $this->{$name}[$idLang] : '';
                    $vals[] = '"' . Db::getInstance()->escape($value) . '"';
                }
            }
            $sql = 'INSERT INTO `' . $table . '` (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $vals) . ')';
            Db::getInstance()->execute($sql);
        }
    }
}
