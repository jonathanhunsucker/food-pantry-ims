<?php

class DropOffsController extends Controller {

    public function indexAction() {
        return array(
            "products" => (new Product)->findAll(),
            "sources" => (new Source)->findAll(),
        );
    }
    
    public function dropOffPostAction() {
        $names = $_REQUEST["prod_name"];
        $sources = $_REQUEST["source_name"];
        $quantities = $_REQUEST["qty"];
        
        $txid = rand();
        $result = Database::get()->query("INSERT INTO Drop_Off_Transaction
            (`Drop_off_Tx_ID`, `Date_of_Drop_Off`)
            VALUES($txid, CURRENT_DATE())
        ");
        if (!$result) {
            flash_error("Failed to add Drop_Off for $name");
            $this->_redirect("/drop-offs/");
        }
        
        for ($i=0 ; $i < count($names) ; $i++) {
            $name = $names[$i];
            $source = $sources[$i];
            $qty = $quantities[$i];
            
            if (!$qty) continue;
            
            $result = Database::get()->query("INSERT INTO Drop_Off
                (`Product_Name`, `Source_Name`, `Drop_Off_Tx_ID`, `Quantity_Dropped`)
                VALUES(\"$name\", \"$source\", \"$txid\", $qty)
            ");
            if (!$result) {
                flash_error("Failed to add Drop_Off for $name");
            } else {
                $result = Database::get()->query("INSERT INTO Product_Quantity
                    (Product_Name, quantity)
                    VALUES (\"$name\", \"$qty\")
                    ON DUPLICATE KEY UPDATE quantity=quantity+$qty;"
                );
                if (!$result) {
                    flash_error("Failed to update inventory for $name");
                }
            }
        }
        
        $this->_redirect("/drop-offs/");
    }
    
}

?>
