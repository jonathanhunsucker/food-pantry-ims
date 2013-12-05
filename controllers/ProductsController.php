<?php

class ProductsController extends Controller {
    
    public function indexAction() {
        $prod_name = $_REQUEST["prod_name"];
        
        if ($prod_name) {
            $products = (new Product)->whereLike("name", $prod_name);
        } else {
            $products = (new Product)->findAll();
        }
        
        return array(
            "products" => $products,
            "prod_name" => $prod_name,
        );
    }
    
}

?>
