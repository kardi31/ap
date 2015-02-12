<?php

/**
 * Affilateprogram_Service_Affilateprogram
 *
@author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Affilateprogram_Service_Affilateprogram extends MF_Service_ServiceAbstract {
    
    protected $partnerTable;
    protected $partnerOrdersTable;
    
    public function init() {
        $this->partnerTable = Doctrine_Core::getTable('Affilateprogram_Model_Doctrine_Partner');
        $this->partnerOrdersTable = Doctrine_Core::getTable('Affilateprogram_Model_Doctrine_PartnerOrders');
        parent::init();
    }
    
    public function getPartner($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->partnerTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getPartnerForm(Affilateprogram_Model_Doctrine_Partner $partner = null) {
        $form = new Affilateprogram_Form_Partner();
        if(null != $partner) { 
            $form->populate($partner->toArray());
        }
        return $form;
    }
    
    public function getCheckCommissionForm() {
        $form = new Affilateprogram_Form_Commission();
        return $form;
    }
    
    public function savePartnerWithNewNumberFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        
        $partner = $this->partnerTable->getRecord();
        $partner->reference_number = MF_Text::createUniqueTableReferenceNumber('Affilateprogram_Model_Doctrine_Partner', $this->generateReferenceNumber(), $partner->getId());
        $partner->fromArray($values);
        $partner->save();
    }
    
    public function generateReferenceNumber($length = 12) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $ret = '';
        for($i = 0; $i < $length; ++$i) {
          $random = str_shuffle($chars);
          $ret .= $random[0];
        }
        return $ret;
    }
    
    public function savePartnerFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        
        if(!$partner = $this->getPartner((int) $values['id'])) {
            $partner = $this->partnerTable->getRecord();
        }
         
        $partner->fromArray($values);
        $partner->save();
        
        return $partner;
    }
    
    public function saveOrderForPartner($orderId, $partnerId){
        $partnerOrder = $this->partnerOrdersTable->getRecord();
        $partnerOrder->setOrderId($orderId);
        $partnerOrder->setPartnerId($partnerId);
        $partnerOrder->save();
    }
    
    public function removePartner(Affilateprogram_Model_Doctrine_Partner $partner) {
        $partner->delete();
    }
    
    public function getOrdersForPartner($partnerId, $values, $hydrationMode = Doctrine_Core::HYDRATE_ARRAY) {
        if(strlen($values['start_date'])) {
            $date = new Zend_Date($values['start_date'], 'dd/MM/yyyy HH:mm');
            $values['start_date'] = $date->toString('yyyy-MM-dd HH:mm:00');
        } 
        if(strlen($values['end_date'])) {
            $date = new Zend_Date($values['end_date'], 'dd/MM/yyyy HH:mm');
            $values['end_date'] = $date->toString('yyyy-MM-dd HH:mm:00');
        } 
        $q = $this->partnerTable->getOrdersForPartnerQuery($partnerId, $values);
        return $q->execute(array(), $hydrationMode);
    }
    
}
?>