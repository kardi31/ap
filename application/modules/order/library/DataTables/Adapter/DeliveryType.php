<?php

/**
 * Order_DataTables_Adapter_DeliveryType
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_DataTables_Adapter_DeliveryType extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('dt');
        $q->select('dt.*');
        return $q;
    }
}

