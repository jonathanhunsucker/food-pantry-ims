<?php

class Config {
    public $view;
    
    function __construct() {
        $this->view = (object) array(
            "fragments" => "",
            "title" => "Food Pantry",
        );
        $this->view->fragments = (object) array(
            "head" => $this->getFragment("head"),
            "header" => $this->getFragment("header"),
            "footer" => $this->getFragment("footer"),
        );
    }
    
    public function getFragment($filename) {
        return file_get_contents(PROJECT_ROOT . "/views/_fragments/$filename.html");
    }
}

?>
