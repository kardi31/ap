<?php

/**
 * Order_DataTables_Adapter_Item
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_DataTables_Adapter_Item extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('it');
        $q->select('it.*');
        $q->addSelect('pro.*');
        $q->leftJoin('it.Product pro');
        $q->andWhere('it.order_id = ?', array($this->request->getParam('id')));
        return $q;
    }
}

