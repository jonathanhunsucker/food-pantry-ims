<?php

class ProductQuantity extends Model {
    
    public function setUp() {
        $this->hasPrimaryKey("Product_Name as name");
        $this->hasColumn("quantity");
    }
    
}

?>
