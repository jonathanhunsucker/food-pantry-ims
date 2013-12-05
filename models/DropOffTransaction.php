<?php

class DropOffTransction extends Model {
    
    public function setUp() {
        $this->hasColumn("Drop_off_Tx_ID as txid");
        $this->hasColumn("Date_of_Drop_Off as date");
    }
    
}

?>
