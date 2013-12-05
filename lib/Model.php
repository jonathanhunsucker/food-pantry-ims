<?php

class Model {
    protected static $_table_names = array();
    protected static $_table_defs = array();
    protected static $_table_defs_lookup = array();
    protected static $_table_key_defs = array();
    protected static $_table_refs = array();
    
    protected $_table_name;
    protected $_values = array();
    protected $_dirty_values = array();
    protected $_foreigns = array();
    public $_from_table = false;
    
    function __construct() {
        $this->_table_name = self::getTableName();
    }
    
    public function setUp() {}
    
    public function save() {
        $values = implode(", ", array_map(array($this, "ident"), array_keys($this->_dirty_values)));
        $values = implode(", ", array_map(function ($col) {
            return "\"" . $col .  "\"";
        }, array_values($this->_dirty_values)));
        
        if (!$this->_from_table) {
            $this->_insert();
        } else {
            $this->_save();
        }
    }
    
    public function _save() {
        $query = "UPDATE `" . $this->_table_name . "` SET ";//{$columns}) VALUES(" . $values . ")";
        $updates = array();
        foreach ($this->_dirty_values as $key => $value) {
            $updates[] = $this->ident($key) . "=\"{$value}\"";
        }
        $updates = implode(", ", $updates);
        $query .= $updates;
        $query .= " WHERE ";
        $conditions = $this->getKeyWhere();
        $where_conditions = [];
        foreach ($conditions as $column => $value) {
            $where_conditions[] = $this->ident($column) . "='" . $value . "'";
        }
        $query .= implode(" AND ", $where_conditions);
        $result = Database::get()->query($query);
    }
    
    public function _insert() {
        $columns = array_keys($this->getAllColumns());
        $real_columns = implode(", ", array_map(function ($col) {
            return $this->ident($col);
        }, $columns));
        $values = implode(", ", array_map(function ($col) {
            return "\"" . $this->$col . "\"";
        }, $columns));
        $query = "INSERT INTO `" . $this->_table_name . "` ({$real_columns}) VALUES(" . $values . ")";
        $result = Database::get()->query($query);
        
        for ($i=0 ; $i < count($columns) ; $i++) {
            $this->{$this->getCommonNameFor($columns[$i])} = $values[$i];
        }
    }
    
    public function delete() {
        $query = "DELETE FROM `" . $this->_table_name . "` WHERE ";
        $conditions = $this->getKeyWhere();
        $where_conditions = [];
        foreach ($conditions as $column => $value) {
            $where_conditions[] = $this->ident($column) . "='" . $value . "'";
        }
        $query .= implode(" AND ", $where_conditions);
        $result = Database::get()->query($query);
        return $result;
    }
    
    public function findWhere($conditions) {
        $query = "SELECT * FROM `" . $this->_table_name . "` WHERE ";
        $where_conditions = array();
        foreach ($conditions as $column => $value) {
            $where_conditions[] = $this->ident($column) . "='" . $value . "'";
        }
        $query .= implode(" AND ", $where_conditions);
        $results = Database::get()->fetch($query);
        
        return $this->yieldAll($results);
    }
    
    public function find() {
        $key_values = func_get_args();
        $keys = $this->getPrimaryKeys();
        if (count($keys) !== count($key_values)) throw new Exception($this->_table_name . " requires " . count($keys_values) . "-many keys but only " . count($key_values) . " were provided");
        $conditions = array();
        for ($i=0 ; $i < count($keys) ; $i++) $conditions[$keys[$i]] = $key_values[$i];
        return $this->findWhere($conditions)[0];
    }
    
    public function whereLike($column, $value) {
        $query = "SELECT * FROM `" . $this->_table_name . "` WHERE ";
        $query .= $this->ident($column) . " LIKE \"%$value%\"";
        $results = Database::get()->fetch($query);
        return $this->yieldAll($results);
    }
    
    public function getKeyWhere() {
        $keys = $this->getPrimaryKeys();
        $conditions = array();
        for ($i=0 ; $i < count($keys) ; $i++) $conditions[$keys[$i]] = $this->{$keys[$i]};
        return $conditions;
    }
    
    public function getData() {
        return $this->_dirty_values;
    }
    
    public function yield($fields) {
        $class = get_class($this);
        $model = new $class();
        foreach ($fields as $name => $value) $model->{$this->getCommonNameFor($name)} = $value;
        $model->_from_table = true;
        return $model;
    }
    
    public function yieldAll($fieldss) {
        $all = array();
        foreach ($fieldss as $fields) $all[] = $this->yield($fields);
        return $all;
    }
    
    public function findAll() {
        $fieldss = Database::get()->fetch("SELECT * FROM `%s`", $this->_table_name);
        return $this->yieldAll($fieldss);
    }
    
    public function hasColumn($column_name, $column_definition="") {
        $names = $this->extractActualAndCommonNames($column_name, true);
        self::$_table_defs[$this->_table_name]["column"][$names[1]] = $column_definition;
        return $names;
    }
    
    public function getColumns() {
        return self::$_table_defs[$this->_table_name]["column"];
    }
    
    public function _hasPrimaryKey($key_column_name) {
        list($actual_key, $common_key) = $this->extractActualAndCommonNames($key_column_name, true);
        self::$_table_key_defs[$this->_table_name]["primary_key"][] = $common_key;
    }
    
    public function hasPrimaryKey() {
        foreach (func_get_args() as $def) {
            $this->_hasPrimaryKey($def);
        }
    }
    
    public function getPrimaryKeys() {
        return self::$_table_key_defs[$this->_table_name]["primary_key"];
    }
    
    public function getAllColumns() {
        $columns = $this->getColumns();
        $keys = $this->getPrimaryKeys();
        if (count($keys) > 0) $columns = array_merge(array_flip($keys), $columns);
        return $columns;
    }
    
    /**
     * $column_name in format either `ActualName as common` or `common`
     * $reference is array($class_name, $column_name)
     */
    public function hasForeignKey($column_def, $reference, $common_name, $quantity=2) {
        list($actual, $common) = $this->extractActualAndCommonNames($column_def, false);
        if (!key_exists($common, self::$_table_defs_lookup[$this->_table_name])) list($actual, $common) = $this->hasColumn($column_def);
        self::$_table_refs[$this->_table_name][$common_name] = array("common" => $common, "reference" => $reference, "quantity" => $quantity);
    }
    
    public function hasForeignKeyOnce($column_def, $reference, $common_name) {
        return $this->hasForeignKey($column_def, $reference, $common_name, 1);
    }
    
    /**
     * Support definition of column name in the format of SuperLongActualColumnName as muchbetter
     * by using a lookup table
     */
    public function extractActualAndCommonNames($name, $set=false) {
        list($actual, $common) = explode(" as ", $name);
        if (!$common) $common = $actual;
        if ($set) $this->updateActualAndCommonNames($actual, $common);
        return array($actual, $common);
    }
    
    public function updateActualAndCommonNames($actual, $common) {
        self::$_table_defs_lookup[$this->_table_name][$common] = $actual;
    }
    
    public function commonNameIsDefined($common_name) {
        return key_exists($common_name, self::$_table_defs_lookup[$this->_table_name]);
    }
    
    public function getCommonNameFor($actual_name) {
        return array_search($actual_name, self::$_table_defs_lookup[$this->_table_name]);
    }
    
    public function __get($name) {
        if (key_exists($name, $this->_dirty_values)) {
            return $this->_dirty_values[$name];
        } else if (key_exists($name, $this->_values)) {
            return $this->_values[$name];
        } else if (key_exists($this->_table_name, self::$_table_refs) && key_exists($name, self::$_table_refs[$this->_table_name])) {
            return $this->_getForeign($name);
        }
    }
    
    public function _getForeign($name) {
        if (key_exists($name, $this->_foreigns)) return $this->_foreigns[$name];
        $def = self::$_table_refs[$this->_table_name][$name];//"column" => $common, "reference" => $reference
        $common = $def["common"];
        list($class, $column) = $def["reference"];
        $quantity = $def["quantity"];
        $model = (new $class())->findWhere(array($column => $this->$common));
        if ($quantity === 1) $model = $model[0];
        $this->_foreigns[$name] = $model;
        return $model;
    }
    
    public function __set($name, $value) {
        $this->_dirty_values[$name] = $value;
    }
    
    /**
     * Used by functions that need direct access to the column data without
     * going through __get()
     */
    public function getValue($name) {
        return $this->_dirty_values[$name];
    }
    
    /**
     * DropOffTransaction becomes Drop_Off_Transaction
     * This is used to mitigate having to nest a Transaction model in a directory drop/off/
     * which would have been the result of Framework's autoloading function.
     */
    public static function getTableName() {
        return preg_replace("/(.)([A-Z])/", "$1_$2", get_called_class());
    }
    
    public static function __init_static() {
        $class = get_called_class();
        if ($class === "Model" || key_exists($class, self::$_table_names)) return;
        $model = new $class();
        $model->setUp();
        self::$_table_names[$class] = self::getTableName();
    }
    
    public function ident($name) {
        return "`" . self::$_table_defs_lookup[$this->_table_name][$name] . "`";
    }
    
    public function implodeNIdent($items) {
        return implode(" ", array_map(array($this, "ident"), $items));
    }
}
