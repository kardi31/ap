<?php

/**
 * Order_Service_DeliveryType
 *
@author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_Service_DeliveryType extends MF_Service_ServiceAbstract {
    
    protected $deliveryTypeTable;
    
    public function init() {
        $this->deliveryTypeTable = Doctrine_Core::getTable('Order_Model_Doctrine_DeliveryType');
        parent::init();
    }
    
    public function getDeliveryType($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->deliveryTypeTable->findOneBy($field, $id, $hydrationMode);
    }
    public function getDeliveryTypes() {    
        return $this->deliveryTypeTable->findAll();
    }
    public function getDeliveryTypeForm(Order_Model_Doctrine_DeliveryType $deliveryType = null) {
        $form = new Order_Form_DeliveryType();
        if(null != $deliveryType) { 
            $form->populate($deliveryType->toArray());
        }
        return $form;
    }
    
    public function saveDeliveryTypeFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$deliveryType = $this->getDeliveryType((int) $values['id'])) {
            $deliveryType = $this->deliveryTypeTable->getRecord();
        }
         
        $deliveryType->fromArray($values);
        $deliveryType->save();
        
        return $deliveryType;
    }
    
    public function removeDeliveryType(Order_Model_Doctrine_DeliveryType $deliveryType) {
        $deliveryType->delete();
    }
}
?>