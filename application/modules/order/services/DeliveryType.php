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
    
    public function getAllDeliveryType($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->deliveryTypeTable->getDeliveryTypeQuery();
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getAllDeliveryTypeSorted($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->deliveryTypeTable->getDeliveryTypeQuery();
        $q->addOrderBy('dt.created_at ASC');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getTargetDeliveryTypeSelectOptionsSorted($prependEmptyValue = false) {
        $items = $this->getAllDeliveryTypeSorted();
        $result = array();
        if($prependEmptyValue) {
            $result[''] = '';
        }
        foreach($items as $item) {
            if($item->getId() != 9){
                $result[$item->getId()] = $item->name." - ".$item->price." zł ";
            }
            else{
                $result[$item->getId()] = $item->name;
            }
        }
        return $result;
    }
    
    public function getTargetDeliveryTypeSelectOptions($prependEmptyValue = false) {
        $items = $this->getAllDeliveryType();
        $result = array();
        if($prependEmptyValue) {
            $result[''] = ' ';
        }
        foreach($items as $item) {
                $result[$item->getId()] = $item->name." - ".$item->price." zł";
        }
        return $result;
    }
    
     public function getTargetTypeSelectOptions($prependEmptyValue = false) {
        $result = array();
        if($prependEmptyValue) {
            $result[''] = ' ';
        }
        $result['pobranie'] = "pobranie";
        $result['przelew'] = "przelew";
        $result['brak'] = "brak";

        return $result;
    } 
    
    public function getMinimalPriceForDelivery() {
        $items = $this->getAllDeliveryType();
        $min = $items[0]->getPrice();
        foreach($items as $item):
            if ($item->getPrice() > 0):
                if ($min > $item->getPrice()):
                    $min = $item->getPrice();
                endif;
            endif;
        endforeach;
        return $min;
    }
    
}
?>
