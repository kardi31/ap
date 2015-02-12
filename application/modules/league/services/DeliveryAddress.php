<?php

/**
 * Order_Service_PaymentType
 *
@author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_Service_DeliveryAddress extends MF_Service_ServiceAbstract {
    
    protected $deliveryAddressTable;
    
    public function init() {
        $this->deliveryAddressTable = Doctrine_Core::getTable('Order_Model_Doctrine_DeliveryAddress');
        parent::init();
    }
    
    public function getDeliveryAddress($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->deliveryAddressTable->findOneBy($field, $id, $hydrationMode);
    }
    public function getPaymentTypes() {    
        return $this->deliveryAddressTable->findAll();
    }
    
    public function getDeliveryAddressForm(Order_Model_Doctrine_PaymentType $paymentType = null) {
        $form = new Order_Form_PaymentType();
        if(null != $paymentType) { 
            $form->populate($paymentType->toArray());
        }
        return $form;
    }
    
    public function saveDeliveryAddressFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$paymentType = $this->getDeliveryAddress((int) $values['id'])) {
            $paymentType = $this->deliveryAddressTable->getRecord();
        }
         
        $paymentType->fromArray($values);
        $paymentType->save();
        
        return $paymentType;
    }
    
    public function removePaymentType(Order_Model_Doctrine_PaymentType $paymentType) {
        $paymentType->delete();
    }
     public function checkDeliveryAddress($values) {
        $delAddress['name'] = $values['last_name']." ".$values['first_name'];
        if($values['difAddress']==1)
        {
            $delAddress['address'] = $values['difstreet']." ".$values['difhouseNr'];
            if(!empty($values['difflatNr'])) $delAddress['address'] .= "/".$values['difflatNr'];
            $delAddress['postal_code'] = $values['difpostal_code'];
            $delAddress['city'] = $values['difcity'];
        }
        else{
            $delAddress['address'] = $values['street']." ".$values['houseNr'];
             if(!empty($values['flatNr'])) $delAddress['address'] .= "/".$values['flatNr'];
            $delAddress['postal_code'] = $values['postal_code'];
            $delAddress['city'] = $values['city'];
        }
        return $delAddress;
    }
}
?>