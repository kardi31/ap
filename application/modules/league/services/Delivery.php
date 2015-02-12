<?php

/**
 * Order_Service_DeliveryType
 *
@author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_Service_Delivery extends MF_Service_ServiceAbstract {
    
    protected $deliveryTypeTable;
    
    public function init() {
        $this->deliveryTable = Doctrine_Core::getTable('Order_Model_Doctrine_Delivery');
        parent::init();
    }
    
    public function getDelivery($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->deliveryTable->findOneBy($field, $id, $hydrationMode);
    }
    public function getDeliverys() {    
        return $this->deliveryTable->findAll();
    }
    public function getDeliveryForm(Order_Model_Doctrine_Delivery $delivery = null) {
        $form = new Order_Form_Delivery();
        if(null != $delivery) { 
            $form->populate($delivery->toArray());
        }
        return $form;
    }
    
    public function saveDeliveryFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$delivery = $this->getDelivery((int) $values['id'])) {
            $delivery = $this->deliveryTable->getRecord();
        }
         
        $delivery->fromArray($values);
        $delivery->save();
        
        return $delivery;
    }
    
    public function removeDelivery(Order_Model_Doctrine_Delivery $delivery) {
        $delivery->delete();
    }
}
?>