<?php

class Bag extends Model {
    
    public function setUp() {
        $this->hasPrimaryKey("Bag_Name as name");
        $this->hasForeignKey("name", array("Holds", "bag_name"), "holds");
    }
    
}

?>
