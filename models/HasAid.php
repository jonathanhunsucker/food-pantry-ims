<?php

class HasAid extends Model {
    
    public function setUp() {
        $this->hasColumn("Name_Of_Aid as name");
        $this->hasColumn("Client_ID as cid");
        //$this->hasForeignKeyOnce("cid", array("Client", "cid"), "client");
        //$this->hasForeignKeyOnce("name", array("FinancialAid", "name"), "finaid");
    }
    
}

?>
