<?php

/**
 * Order_DataTables_Adapter_Coupon
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_DataTables_Adapter_Coupon extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('c');
        $q->select('c.*');
        return $q;
    }
}

