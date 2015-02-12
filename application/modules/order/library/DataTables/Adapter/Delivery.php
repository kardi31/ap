<?php

/**
 * Order_DataTables_Adapter_Delivery
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_DataTables_Adapter_Delivery extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('d');
        $q->select('d.*');
        $q->addSelect('dt.*');
        $q->addSelect('da.*');
        $q->leftJoin('d.DeliveryType dt');
        $q->leftJoin('d.DeliveryAddress da');
        $q->andWhere('d.id = ?', array($this->request->getParam('id')));
        return $q;
    }
}

