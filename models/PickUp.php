<?php

class Pick_Up {
    
    public function setUp() {
        $this->hasColumn("PickUp_Tx_ID as txid");
        $this->hasColumn("Client_ID as cid");
        $this->hasColumn("Bag_Name as bag_name");
    }
    
}

?>
