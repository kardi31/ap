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
        
        $type = $this->createElement('hidden', 'type');
        $type->setDecorators(array('ViewHelper'));
        
        $deliveryStatusId = $this->createElement('select', 'delivery_status_id');
        $deliveryStatusId->setLabel('Delivery status');
        $deliveryStatusId->setDecorators(self::$selectDecorators);
        
        $paymentStatusId = $this->createElement('select', 'payment_status_id');
        $paymentStatusId->setLabel('Payment status');
        $paymentStatusId->setDecorators(self::$selectDecorators);
        
        $orderStatusId = $this->createElement('select', 'order_status_id');
        $orderStatusId->setLabel('Order status');
        $orderStatusId->setDecorators(self::$selectDecorators);
        
        $deliveryCost = $this->createElement('text', 'delivery_cost');
        $deliveryCost->setLabel('Delivery cost');
        $deliveryCost->setDecorators(self::$textDecorators);
        $deliveryCost->setAttrib('class', 'span8');
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
            $orderStatusId,
            $deliveryStatusId,
            $paymentStatusId,
            $deliveryCost,
            $type,
            $submit
        ));
    }
    
}

