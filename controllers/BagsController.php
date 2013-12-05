<?php

class BagsController extends Controller {
    
    protected $bypass_date_requirement = false;
    
    public function editsNotAllowedMessage() {
        return "<b>Notice</b>: Cannot make changes until " . date('M t \a\f\t\e\r 5\P\M');
    }
    
    public function checkIfCanEdit() {
        if (!$this->canEditBag()) {
            flash_warning($this->editsNotAllowedMessage());
        }
    }
    
    public function canEditBag() {
        return $this->isAfterCloseOfBusinessOnLastDayOfMonth() || $this->bypass_date_requirement;
    }
    
    protected function isAfterCloseOfBusinessOnLastDayOfMonth() {
        return date("t") === date("j") && date("G") >= 17;
    }
    
    public function indexAction() {
        return array(
            "bags" => (new Bag)->findAll(),
        );
    }
    
    public function editAction() {
        $this->checkIfCanEdit();
        $name = $_REQUEST["name"];
        $bag = (new Bag)->find($name);
        return array(
            "bag" => $bag,
            "products" => (new Product)->findAll(),
        );
    }
    
    public function editItemPostAction() {
        if (!$this->canEditBag()) {
            flash_error($this->editsNotAllowedMessage());
            $this->goBackToEditPage();
        }
        
        $bag_name = $_REQUEST["bag_name"];
        $prod_name = $_REQUEST["prod_name"];
        $qty = $_REQUEST["qty"];
        
        $hold = (new Holds)->findWhere(array(
            "bag_name" => $bag_name,
            "prod_name" => $prod_name,
        ))[0];
        
        if ($qty == 0) {
            try {
                $hold->delete();
                flash_success("Deleted item {$prod_name} from bag");
            } catch (Exception $e) {
                flash_error("Error while deleting item: " . $e->getMessage());
            }
        } else {
            $hold->prev_qty = $hold->curr_qty;
            $hold->curr_qty = $qty;
            
            try {
                $hold->save();
                flash_success("Updated item {$prod_name} to quantity {$qty}");
            } catch (Exception $e) {
                flash_error("Error while updating item: " . $e->getMessage());
            }
        }
        
        $this->goBackToEditPage();
    }
    
    public function addItemPostAction() {
        if (!$this->canEditBag()) {
            flash_error($this->editsNotAllowedMessage());
            $this->goBackToEditPage();
        }
        
        $name = $_REQUEST["prod_name"];
        $bag = $_REQUEST["bag_name"];
        $qty = $_REQUEST["qty"];
        
        if (!$qty || !$bag || !$name) {
            flash_error("Failed to save new item. Lacking quantity, or product name.");
            $this->goBAckToEditPage();
        }
        
        $holds = new Holds();
        $holds->bag_name = $bag;
        $holds->prod_name = $name;
        $holds->curr_qty = $qty;
        $holds->prev_qty = 0;
        
        try {
            $holds->save();
            flash_success("Successfully added $qty of $name to $bag");
        } catch (Exception $e) {
            flash_error("Error adding product to bag: {$e->getMessage()}");
        }
        
        $this->goBackToEditPage();
    }
    
    protected function goBackToEditPage() {
        $this->_redirect("/bags/edit?name=" . urlencode($_REQUEST["bag_name"]));
    }
    
}

?>
