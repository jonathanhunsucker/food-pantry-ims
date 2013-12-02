<?php

class Controller {
    public function preRun() {
        $params = array();
        if ($this->hasUser()) $params["user"] = $this->getUser();
        return $params;
    }
    
    public function setUser($user) {
        $_SESSION["user"] = $user;
    }
    
    public function hasUser() {
        return key_exists("user", $_SESSION);
    }
    
    public function getUser() {
        return $_SESSION["user"];
    }
    
    public function requireUser() {
        if (!$this->hasUser()) {
            flash_info("Please log in to view that page");
            $this->_redirect("/");
        }
    }
    
    public function dispatch($method) {
        $params = $this->preRun();
        $controller_params = $this->$method();
        if (is_array($controller_params)) {
            $params = array_merge($params, $controller_params);
        } else {
            $params = $controller_params;
        }
        return $params;
    }
    
    public function _redirect($location) {
        header("Location: http://" . $_SERVER["HTTP_HOST"] . $location);
        exit;
    }
}

?>
