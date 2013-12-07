<?php

class ClientsController extends Controller {
    
    public function preRun() {
        $params = parent::preRun();
        $this->requireUser();
        return $params;
    }
    
    public function indexAction() {
        $lname = $_REQUEST["lname"];
        $phone = $_REQUEST["phone"];
        
        $conditions = array();
        if ($lname) $conditions["lname"] = $lname;
        if ($phone) $conditions["phone"] = $phone;
        
        if (count($conditions) > 0) {// instead of strict where, use LIKE, which is more search-like
            $clients = (new Client)->findWhere($conditions);
        } else {
            $clients = (new Client)->findAll();
        }
        
        return array(
            "lname" => $lname,
            "phone" => $phone,
            "clients" => $clients,
        );
    }
    
    protected function prepareCreateRequest() {
        if (!$_REQUEST["start"]) $_REQUEST["start"] = date("Y-m-d");// TODO: this might or might not actually do anything, since $_REQUEST may or may be overwrite-able
    }
    
    public function createAction() {
        $this->prepareCreateRequest();

        return array_merge($_REQUEST, array(
            "bags" => (new Bag)->findAll(),
            "finaids" => (new FinancialAid)->findAll(),
        ));
    }
    
    public function createPostAction() {
        $this->prepareCreateRequest();
        
        $cid = rand(); // pray for no collisions
        
        $client = new Client();
        $client->cid = $cid;
        foreach (array(
            "fname", "lname", "gender", "phone",  "street", "aptno",
            "city", "state", "zip", "pickupday", "start", "dob"
        ) as $datum) {
            $client->$datum = $_REQUEST[$datum];
        }
        $client->bag_type = $_REQUEST["bagname"];
        
        try {
            $client->save();
        } catch (Exception $e) {
            dump($e);
            $this->_redirect("/clients/create");
        }
        
        foreach ($_REQUEST["finaid"] as $finaid_name) {
            $aid = new HasAid();
            $aid->name = $finaid_name;
            $aid->cid = $cid;
            $aid->save();
        }
        
        $this->_redirect("/clients/add-family?cid=" . $cid);
    }
    
    public function addFamilyAction() {
        $cid = $_REQUEST["cid"];
        $client = (new Client)->find($cid);
        
        return array_merge($_REQUEST, array(
            "client" => $client,
        ));
    }
    
    public function addFamilyPostAction() {
        $cid = $_REQUEST["cid"];
        $client = (new Client)->find($cid);
        $member = new FamilyMember();
        $member->cid = $client->cid;
        $member->fname = $_REQUEST["fname"];
        $member->lname = $_REQUEST["lname"];
        $member->dob = $_REQUEST["dob"];
        
        try {
            $member->save();
            flash_success("Added family member <b>" . $member->fname . " " . $member->lname . "</b>");
        } catch (Exception $e) {
            flash_error($e->getMessage());
        }
        
        $this->_redirect("/clients/add-family?cid=" . $cid);
    }
}

?>
