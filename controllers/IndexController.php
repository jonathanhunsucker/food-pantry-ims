<?php

class IndexController extends Controller {
    public function indexAction() {
        return array();
    }
    
    public function loginPostAction() {
        $username = $_REQUEST["username"];
        $password = $_REQUEST["password"];
        
        $user = (new User)->find($username);
        if (!$user || $user->password !== $password) {
            flash_warning("Incorrect username or password");
            return $this->_redirect("/");
        }
        
        $this->setUser($user);
        return $this->_redirect("/");
    }
    
    public function logoutAction () {
        session_destroy();
        $this->_redirect("/");
    }
}

?>
