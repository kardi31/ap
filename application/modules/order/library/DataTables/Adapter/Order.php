<?php

/**
 * Order_DataTables_Adapter_Order
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_DataTables_Adapter_Order extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('or');
        $q->select('or.*');
        $q->addSelect('u.*');
        $q->addSelect('c.*');
        $q->addSelect('p.*');
        $q->addSelect('ct.*');
        $q->addSelect('pt.*');
        $q->addSelect('pr.*');
        $q->addSelect('da.*');
        $q->leftJoin('or.User u');
        $q->leftJoin('u.Profile pr');
        $q->leftJoin('or.Delivery c');
        $q->leftJoin('c.DeliveryType dt');
        $q->leftJoin('c.DeliveryAddress da');
        $q->leftJoin('or.Payment p');
        $q->leftJoin('p.PaymentType pt');
        return $q;
    }
}

