<?php

/**
 * Order_Service_Payment
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
abstract class Order_Service_Payment extends MF_Service_ServiceAbstract {
    
    protected $paymentTable;
    
    public function init() {
        $this->paymentTable = Doctrine_Core::getTable('Order_Model_Doctrine_Payment');
        parent::init();
    }
    
    public function getFullPayment($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->paymentTable->getFullPaymentQuery();
        $q->andWhere('p.' . $field . ' = ?', $id);
        return $q->fetchOne(array(), $hydrationMode);
    }
    
    public function getPayment($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->paymentTable->findOneBy($field, $id, $hydrationMode);
    }
}

