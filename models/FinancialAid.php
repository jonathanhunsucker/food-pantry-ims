<?php

class FinancialAid extends Model {
    
    public function setUp() {
        $this->hasPrimaryKey("Aid_Name as name");
        $this->hasColumn("Type_of_Aid as type");
    }
    
}

?>
