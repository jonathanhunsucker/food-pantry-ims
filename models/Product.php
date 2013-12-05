<?php

class Product extends Model {
    
    public function setUp() {
        $this->hasPrimaryKey("Product_Name as name");
        $this->hasColumn("Cost as cost");
        $this->hasColumn("Source_Name as source_name");
        
        $this->hasForeignKeyOnce("source_name", array("Source", "name"), "source");
        $this->hasForeignKeyOnce("name", array("ProductQuantity", "name"), "quantity");
    }
    
    public function costFormatted() {
        return '$' . money_format('%i', $this->getValue("cost")/100);
    }
    
    public function getQuantity() {
        $qty = $this->quantity->quantity;
        if ($qty === null) $qty = 0;
        return $qty;
    }
    
    public function __get($name) {
        if ($name == "cost") return $this->costFormatted();
        if ($name == "qty") return $this->getQuantity();
        return parent::__get($name);
    }
    
}

?>
