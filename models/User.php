<?php

class User extends Model {
    
    public function setUp() {
        $this->hasPrimaryKey("Username as username");
        $this->hasColumn("Password as password");
        $this->hasColumn("FirstName as fname");
        $this->hasColumn("LastName as lname");
        $this->hasColumn("Email as email");
        $this->hasColumn("Type_Of_User as type");
    }
    
}

?>
