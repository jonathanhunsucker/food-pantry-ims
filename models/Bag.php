<?php

class Bag extends Model {
    
    public function setUp() {
        $this->hasPrimaryKey("Bag_Name as name");
        $this->hasForeignKey("name", array("Holds", "bag_name"), "holds");
        $this->hasForeignKey("name", array("Client", "bag_type"), "clients");
    }
    
    public function getCost() {
        $result = Database::get()->fetch("SELECT sum(Holds.Current_Mnth_Qty * Product.Cost) as cost FROM Holds NATURAL JOIN Product WHERE Holds.Bag_Name=\"" . $this->name . "\"");
        $cost = $result[0]["cost"];
        if (!$cost) $cost = 0;
        $cost = '$' . money_format('%i', $cost/100);
        return $cost;
    }
    
    public function __get($name) {
        if ($name == "cost") return $this->getCost();
        return parent::__get($name);
    }
    
}

?>
