<?php

class ServiceReportController extends Controller {
    
    public function indexAction() {
        $month = $_REQUEST["view"] == "prev" ? "Last" : "Current";
        
        $data = array();
        
        $weeks = array(
            array(1, 7),
            array(8, 14),
            array(15, 21),
            array(22, 100),
        );
        
        $ranges = array(
            "under18" => function ($table) {
                return "$table.Date_Of_Birth > DATE_SUB(NOW(), INTERVAL 18 YEAR)";
            },
            "midage" => function ($table) {
                return "$table.Date_Of_Birth > DATE_SUB(NOW(), INTERVAL 65 YEAR) AND $table.Date_Of_Birth < DATE_SUB(NOW(), INTERVAL 18 YEAR)";
            },
            "over65" => function ($table) {
                return "$table.Date_Of_Birth < DATE_SUB(NOW(), INTERVAL 65 YEAR)";
            }
        );
        
        $tables = array(
            "Client" => "",
            "Family_Member" => "LEFT JOIN Client ON Client.Client_ID=Family_Member.Client_ID"
        );
        
        for ($i=1 ; $i <= count($weeks) ; $i++) {
            list($start_day, $end_day) = $weeks[$i-1];
            $data[$i] = array();
            foreach ($ranges as $name => $condition) {
                $count = 0;
                foreach ($tables as $table => $join) {
                    $count += Database::get()->fetch("SELECT count(*) as n FROM $table $join WHERE PickUpDay BETWEEN $start_day AND $end_day AND " . $condition($table))[0]["n"];
                }
                $data[$i][$name] = $count;
            }
            $data[$i]["nHouseholds"] = Database::get()->fetch("SELECT count(*) as n FROM Client WHERE PickUpDay BETWEEN $start_day AND $end_day")[0]["n"];
            $data[$i]["foodCost"] = Database::get()->fetch("select sum(Holds." . $month . "_Mnth_Qty * Product.Cost) as n from Client left join Holds on Client.Bag_Type = Holds.Bag_Name left join Product on Holds.Product_Name = Product.Product_Name where Client.PickUpDay BETWEEN $start_day AND $end_day")[0]["n"];
        }
        
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
