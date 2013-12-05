<?php

class ServiceReportController extends Controller {
    
    public function indexAction() {
        $month = $_REQUEST["view"] == "prev" ? "Last" : "Current";
        $data = Database::get()->fetch("SELECT 
            (SELECT 
                FLOOR(pickupday/7)+1
            ) as week,
            (SELECT count(Client.Client_ID) 
                FROM Client 
                WHERE
                FLOOR(pickupday/7)+1=week
            ) as nHouseholds, 
            (SELECT count(*)
                FROM Client LEFT JOIN Family_Member ON Client.Client_ID=Family_Member.Client_ID
                WHERE
                DATEDIFF(CURDATE(), Family_Member.Date_Of_Birth) < 365*18 AND 
                FLOOR(pickupday/7)+1=week
            ) as under18, 
            (SELECT count(*)
                FROM Client LEFT JOIN Family_Member ON Client.Client_ID=Family_Member.Client_ID
                WHERE
                DATEDIFF(CURDATE(), Family_Member.Date_Of_Birth) > 365*18 AND
                DATEDIFF(CURDATE(), Family_Member.Date_Of_Birth) < 365*64 AND 
                FLOOR(pickupday/7)+1=week
            ) as midage, 
            (SELECT count(*)
                FROM Client LEFT JOIN Family_Member ON Client.Client_ID=Family_Member.Client_ID
                WHERE
                DATEDIFF(CURDATE(), Family_Member.Date_Of_Birth) > 365*65 AND 
                FLOOR(pickupday/7)+1=week
            ) as over65, 
            (SELECT sum(Product.cost * Holds." . $month . "_Mnth_Qty)
                FROM Client 
                LEFT JOIN Holds ON Holds.Bag_Name=Client.Bag_Type
                LEFT JOIN Product ON Product.Product_Name=Holds.Product_Name
                WHERE FLOOR(pickupday/7)+1=week
            ) as foodCost
        FROM Client;");
        
        $temp_data = array();
        foreach ($data as $value) {
            $week = $value["week"];
            unset($value["week"]);
            $temp_data[$week] = $value;
        }
        $data = $temp_data;
        
        return array(
            "data" => $data
        );
    }
    
    public function groceryListAction() {
        $data = Database::get()->fetch("SELECT Product.Product_Name, 
        (SELECT sum(Current_Mnth_Qty) 
            FROM Holds 
            WHERE Holds.Product_Name=Product.Product_Name
        ) AS quantity, 
        (SELECT sum(Last_Mnth_Qty) 
            FROM Holds 
            WHERE Holds.Product_Name=Product.Product_Name
        ) AS last_month_quantity
        FROM Product;");
        
        return array(
            "data" => $data,
        );
    }
    
}

?>
