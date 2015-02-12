<?php

/**
 * Order_Service_PaymentType
 *
@author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_Service_PaymentType extends MF_Service_ServiceAbstract {
    
    protected $paymentTypeTable;
    
    public function init() {
        $this->paymentTypeTable = Doctrine_Core::getTable('Order_Model_Doctrine_PaymentType');
        parent::init();
    }
    
    public function getPaymentType($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->paymentTypeTable->findOneBy($field, $id, $hydrationMode);
    }
    public function getPaymentTypes() {    
        return $this->paymentTypeTable->findAll();
    }
    
    public function getPaymentTypeForm(Order_Model_Doctrine_PaymentType $paymentType = null) {
        $form = new Order_Form_PaymentType();
        if(null != $paymentType) { 
            $form->populate($paymentType->toArray());
        }
        return $form;
    }
    
    public function savePaymentTypeFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$paymentType = $this->getPaymentType((int) $values['id'])) {
            $paymentType = $this->paymentTypeTable->getRecord();
        }
         
        $paymentType->fromArray($values);
        $paymentType->save();
        
        return $paymentType;
    }
    
    public function removePaymentType(Order_Model_Doctrine_PaymentType $paymentType) {
        $paymentType->delete();
    }
}
?>