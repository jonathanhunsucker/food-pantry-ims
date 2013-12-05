<?php

class Pick_Up_Transaction {
    
    public function setUp() {
        $this->hasColumn("Pick_Up_Transaction_ID as txid");
        $this->hasColumn("Date_Of_Pick_Up as date");
    }
    
}

?>
