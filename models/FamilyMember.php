<?php

class FamilyMember extends Model {
    public function setUp() {
        $this->hasPrimaryKey("Client_ID as cid");
        $this->hasColumn("FirstName as fname");
        $this->hasColumn("LastName as lname");
        $this->hasColumn("Date_Of_Birth as dob");
        $this->hasColumn("Gender as gender");
    }
}
