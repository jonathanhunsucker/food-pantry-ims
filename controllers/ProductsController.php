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
    
    public function createAction() {
        return array_merge($_REQUEST, array(
            "sources" => (new Source)->findAll(),   
        ));
    }
    
    public function createPostAction() {
        $name = $_REQUEST["name"];
        $source = $_REQUEST["source"];
        $cost = $_REQUEST["cost"];
        
        $product = new Product();
        $product->name = $name;
        $product->source_name = $source;
        $product->cost = $cost*100;// we store in cents
        
        try {
            $product->save();
            flash_success("Successfully saved product $name");
        } catch (Exception $e) {
            flash_error("Failed to save product $name: " . $e->getMessage());
        }
        
        $this->_redirect("/products/create");
    }
    
}

?>
