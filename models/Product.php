<?php

class Product extends Model {
    
    public function setUp() {
        $this->hasPrimaryKey("Product_Name as name");
        $this->hasColumn("Cost as cost");
        $this->hasColumn("Source_Name as source_name");
        
        $this->hasOne("Source by source_name as source");
    }
    
}

?>
