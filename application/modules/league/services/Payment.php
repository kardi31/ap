<?php

/**
 * Order_Service_PaymentType
 *

 */
class Order_Service_Payment extends MF_Service_ServiceAbstract {
    
    protected $paymentTable;
    
    public function init() {
        $this->paymentTable = Doctrine_Core::getTable('Order_Model_Doctrine_Payment');
        parent::init();
    }
    
    public function getPaymentType($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->paymentTable->findOneBy($field, $id, $hydrationMode);
    }
    public function getPaymentTypes() {    
        return $this->paymentTable->findAll();
    }
    
    public function getPaymentTypeForm(Order_Model_Doctrine_PaymentType $payment = null) {
        $form = new Order_Form_PaymentType();
        if(null != $payment) { 
            $form->populate($payment->toArray());
        }
        return $form;
    }
    
    public function savePaymentFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$payment = $this->getPaymentType((int) $values['id'])) {
            $payment = $this->paymentTable->getRecord();
        }
         
        $payment->fromArray($values);
        $payment->save();
        
        return $payment;
    }
    
    public function removePaymentType(Order_Model_Doctrine_PaymentType $payment) {
        $payment->delete();
    }
}
?>