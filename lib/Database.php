<?php

class DatabaseException extends Exception {
    function __construct() {
        parent::__construct(mysql_error());
    }
}

class Database {
    private static $_database = "CS_4400_Food_Pantry";
    private static $_host = "localhost:3306";
    private static $_user = "cs4400";
    private static $_password = "password";
    private static $_session;
    
    private $_link;
    
    public function __construct() {
        $this->_link = mysql_connect(self::$_host, self::$_user, self::$_password);
        if (!$this->_link) throw new DatabaseException();
        
        $selected = mysql_select_db(self::$_database);
        if (!$selected) throw new DatabaseException();
        
    }
    
    /**
     * Call with either $sql being an array, which will go directly to _formulate_query
     * Or with arguments in format of $sql, $firstPercentS, $secondPercentS, etc
     */
    public function query($sql) {
        $args = is_array($sql) ? $sql : func_get_args();
        $query = call_user_func_array(array($this, "_formulate_query"), $args);
        $result = mysql_query($query);
        if (!$result) throw new Exception("Invalid query: " . mysql_error());
        return $result;
    }
    
    public function _formulate_query($sql/*, $arg0, $arg1...*/) {
        $args = func_get_args();
        $sql = array_shift($args);
        $args = array_map("mysql_real_escape_string", $args);
        array_unshift($args, $sql);
        return rtrim(call_user_func_array("sprintf", $args), ";") . ";";
    }
    
    public function _fetch($result) {
        $rows = array();
        while ($row = mysql_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }
    
    public function fetch($sql) {
        return $this->_fetch($this->query(func_get_args()));
    }
    
    public static function getMySqlErrorException() {
        return new Exception(mysql_error());
    }
    
    public static function get() {
        if (!self::$_session) self::$_session = new Database();
        return self::$_session;
    }
    
    public function findAll($model_name) {
        $model = new $model_name();
        return $model->findAll();
    }
    
    public function find($model_name, $key) {
        $model = new $model_name();
        return $model->find($key);
    }
    
    
    
}

?>
