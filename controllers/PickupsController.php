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
        // TODO update inventory accordingly
        // set actual pickup date
        
        flash_success("Woot! Just saved that Pickup and adjusted Inventory.");
        $this->_redirect("/pickups/?pickupday=" . $client->pickupday);
    }
}

?>
