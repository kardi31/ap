<?php

/**
 * Order_Service_OrderStatus
 *
@author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_Service_OrderStatus extends MF_Service_ServiceAbstract {
    
    protected $orderStatusTable;
    
    public function init() {
        $this->orderStatusTable = Doctrine_Core::getTable('Order_Model_Doctrine_OrderStatus');
        parent::init();
    }
    
    public function getOrderStatus($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->orderStatusTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getOrderStatusForm(Order_Model_Doctrine_OrderStatus $orderStatus = null) {
        $form = new Order_Form_OrderStatus();
        if(null != $orderStatus) { 
            $form->populate($orderStatus->toArray());
        }
        return $form;
    }
    
    public function saveOrderStatusFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$orderStatus = $this->getOrderStatus((int) $values['id'])) {
            $orderStatus = $this->orderStatusTable->getRecord();
        }
         
        $orderStatus->fromArray($values);
        $orderStatus->save();
        
        return $orderStatus;
    }
    
    public function removeOrderStatus(Order_Model_Doctrine_OrderStatus $orderStatus) {
        $orderStatus->delete();
    }
    
    public function getAllOrderStatus($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->orderStatusTable->getOrderStatusQuery();
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getTargetOrderStatusSelectOptions($prependEmptyValue = false, $type = null) {
        $items = $this->getAllOrderStatus();
        $result = array();
        if($prependEmptyValue) {
            $result[''] = ' ';
        }
        foreach($items as $item) {
            if ($type == $item->type):
                $result[$item->getId()] = $item->name;
            endif; 
        }
        return $result;
    }
    
    public function getTargetTypeOrderStatusSelectOptions($prependEmptyValue = false) {
        $items = $this->getAllOrderStatus();
        $result = array();
        if($prependEmptyValue) {
            $result[''] = ' ';
        }
        $result= array("status zamówienia" => "status zamówienia",
                       "status przesyłki" => "status przesyłki",
                       "status płatności" => "status płatności"
            );
        return $result;
    }
}
?>