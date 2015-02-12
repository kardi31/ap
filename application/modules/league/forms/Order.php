<?php

/**
 * Order_Form_Order
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_Form_Order extends Admin_Form {
    
    public function init() {
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $orderStatusId = $this->createElement('select', 'order_status_id');
        $orderStatusId->setLabel('Order status');
        $orderStatusId->setDecorators(self::$selectDecorators);
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
            $orderStatusId,
            $submit
        ));
    }
    
}

