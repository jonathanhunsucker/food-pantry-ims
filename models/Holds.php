<?php

class Holds extends Model {
    
    public function setUp() {
        $this->hasPrimaryKey("Bag_Name as bag_name");
        $this->hasPrimaryKey("Product_Name as prod_name");
        $this->hasColumn("Current_Mnth_Qty as curr_qty");
        $this->hasColumn("Last_Mnth_Qty as prev_qty");
    }
    
}

?>
