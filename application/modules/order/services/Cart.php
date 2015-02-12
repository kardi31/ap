<?php

/**
 * Order_Service_Cart
 *
@author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_Service_Cart extends MF_Service_ServiceAbstract {
    
    protected $orderTable;
    protected $cart;
    
    public function init() {
        $this->orderTable = Doctrine_Core::getTable('Order_Model_Doctrine_Order');
        parent::init();
    }
    
    public function getCart() {
        if(!$this->cart) {
            $this->cart = new Order_Model_Cart();
        }
        return $this->cart;
    }
    
    public function getDiscountCodeForm() {
        $form = new Order_Form_DiscountCode();
        return $form;
    }
}
?>