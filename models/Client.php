<?php

class Client extends Model {
    
    public function setUp() {
        $this->hasPrimaryKey("Client_ID as cid");
        $this->hasColumn("FirstName as fname");
        $this->hasColumn("LastName as lname");
        $this->hasColumn("Phone as phone");
        $this->hasColumn("Street as street");
        $this->hasColumn("Apt_No as aptno");
        $this->hasColumn("City as city");
        $this->hasColumn("State as state");
        $this->hasColumn("Zipcode as zip");
        $this->hasColumn("PickUpDay as pickupday");
        $this->hasColumn("Start as start");
        $this->hasColumn("Date_Of_Birth as dob");
        $this->hasColumn("Gender as gender");
        $this->hasForeignKeyOnce("Bag_Type as bag_type", array("Bag", "name"), "bag");
        $this->hasForeignKey("cid", array("FamilyMember", "cid"), "family_members");
    }
    
    public function __get($name) {
        if ($name == "size") return count($this->family_members) + 1;
        return parent::__get($name);
    }
    
}

?>
