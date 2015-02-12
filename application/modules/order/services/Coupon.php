<?php

/**
 * Order_Service_Coupon
 *
@author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_Service_Coupon extends MF_Service_ServiceAbstract {
    
    protected $couponTable;
    
    public function init() {
        $this->couponTable = Doctrine_Core::getTable('Order_Model_Doctrine_Coupon');
        parent::init();
    }
    
    public function getCoupon($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->couponTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function checkProductShared($email,$product_id){
        $q = $this->couponTable->createQuery('c');
        $q->andWhere('c.share_product_id = ?', $product_id);
        $q->andWhere('c.share_product_email like ?', $email);
        $q->andWhere('c.sent = 1');
        
        $result = $q->fetchOne(array(),Doctrine_Core::HYDRATE_RECORD);
        if($result)
            return true;
        else
            return false;
    }
    
    public function getCouponForm(Order_Model_Doctrine_Coupon $coupon = null) {
        $form = new Order_Form_Coupon();
        if(null != $coupon) { 
            $form->populate($coupon->toArray());
        }
        return $form;
    }
    
    public function saveCouponsFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        
        if(strlen($values['start_validity_date'])) {
            $date = new Zend_Date($values['start_validity_date'], 'dd/MM/yyyy HH:mm');
            $values['start_validity_date'] = $date->toString('yyyy-MM-dd HH:mm:00');
        }
        
        if(strlen($values['finish_validity_date'])) {
            $date = new Zend_Date($values['finish_validity_date'], 'dd/MM/yyyy HH:mm');
            $values['finish_validity_date'] = $date->toString('yyyy-MM-dd HH:mm:00');
        }
        $counter = (integer)$values['number_of_coupon'];
        for ($i = 1; $i <= $counter; $i++) {
             $coupon = $this->couponTable->getRecord();
             $coupon->code = MF_Text::createUniqueTableCode('Order_Model_Doctrine_Coupon', $this->generateCouponCode(), $coupon->getId());
             $coupon->fromArray($values);
             $coupon->save();
        }
    }
    
    public function saveCouponFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(strlen($values['start_validity_date'])) {
            $date = new Zend_Date($values['start_validity_date'], 'dd/MM/yyyy HH:mm');
            $values['start_validity_date'] = $date->toString('yyyy-MM-dd HH:mm:00');
        }
        
        if(strlen($values['finish_validity_date'])) {
            $date = new Zend_Date($values['finish_validity_date'], 'dd/MM/yyyy HH:mm');
            $values['finish_validity_date'] = $date->toString('yyyy-MM-dd HH:mm:00');
        }
        
        if(!$coupon = $this->getCoupon((int) $values['id'])) {
            $coupon = $this->couponTable->getRecord();
        }
         
        $coupon->fromArray($values);
        $coupon->save();
        
        return $coupon;
    }
    
    public function saveShareCouponFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        
        
        if(!$coupon = $this->getCoupon((int) $values['id'])) {
            $coupon = $this->couponTable->getRecord();
        }
         
        $coupon->fromArray($values);
        $coupon->save();
        
        return $coupon;
    }
    
    public function generateCouponCode($length = 12) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $ret = '';
        for($i = 0; $i < $length; ++$i) {
          $random = str_shuffle($chars);
          $ret .= $random[0];
        }
        return $ret;
    }
    
    public function removeCoupon(Order_Model_Doctrine_Coupon $coupon) {
        $coupon->delete();
    }
    
    public function getTargetCouponTypeSelectOptions($prependEmptyValue = false, $translator){
        $result = array();
        if($prependEmptyValue) {
            $result[''] = ' ';
        }
        $result['percent'] = $translator->translate('percent');
        $result['amount'] = $translator->translate('amount');;
        return $result;
    }
}
?>