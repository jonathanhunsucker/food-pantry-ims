<?php

class PickupsController extends Controller {
    
    public function preRun() {
        $params = parent::preRun();
        $this->requireUser();
        return $params;
    }
    
    public function indexAction() {
        $pickupday = $_REQUEST["pickupday"];
        
        if ($pickupday) {
            $clients = (new Client)->findWhere(array(
                "pickupday" => $pickupday
            ));
        } else {
            $clients = (new Client)->findAll();
        }
        
        return array(
            "pickupday" => $pickupday,
            "clients" => $clients,
        );
    }
    
    public function verifyAction() {
        $cid = $_REQUEST["cid"];
        return array(
            "client" => (new Client)->find($cid),
        );
    }
    
    public function completeAction() {
        $cid = $_REQUEST["cid"];
        $client = (new Client)->find($cid);
        
        $bag_name = $client->bag->name;
        
        $txid = rand();
        $result = Database::get()->query("INSERT INTO Pick_Up_Transaction
            (`Pick_Up_Transaction_ID`, `Date_Of_Pick_Up`)
            VALUES($txid, CURRENT_DATE())
        ");
        if (!$result) {
            $has_error = true;
        } else {
            $result = Database::get()->query("INSERT INTO Pick_Up
                (`PickUp_Tx_ID`, `Client_ID`, `Bag_Name`)
                VALUES('$txid', '$cid', '$bag_name')
            ");
            if (!$result) {
                $has_error = true;
            }
        }
        
        if ($has_error) {
            flash_error("Failed to update pantry inventory for $bag_name");
            $this->_redirect("/pickups/?pickupday=" . $client->pickupday);
        }
        
        $has_error = false;
        
        foreach ($client->bag->holds as $hold) {
            $name = $hold->prod_name;
            $qty = $hold->curr_qty;
            $result = Database::get()->query("INSERT INTO Product_Quantity
                (Product_Name, quantity)
                VALUES ('$name', -$qty)
                ON DUPLICATE KEY UPDATE quantity=quantity-$qty;"
            );
            if (!$result) {
                flash_warning("Failed to update pantry inventory for product $name in $bag_name");
            }
            
        }
        // TODO update inventory accordingly
        // set actual pickup date
        
        if (!$has_error) {
            flash_success("Woot! Just saved that Pickup and adjusted Inventory.");
        }
        $this->_redirect("/pickups/?pickupday=" . $client->pickupday);
    }
}

?>
