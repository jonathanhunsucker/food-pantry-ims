<?php

class Pick_Up_Transaction {
    
    public function setUp() {
        $this->hasColumn("PickUp_Tranaction_ID as txid");
        $this->hasColumn("Date_Of_Pick_Up as date");
    }
    
}

?>
