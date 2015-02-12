<?php

/**
 * Order_DataTables_Adapter_Payment
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_DataTables_Adapter_Payment extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('p');
        $q->select('p.*');
        $q->addSelect('pt.*');
        $q->addSelect('s.*');
        $q->leftJoin('p.PaymentType pt');
        $q->leftJoin('p.Status s');
        $q->andWhere('p.id = ?', array($this->request->getParam('id')));
        return $q;
    }
}

