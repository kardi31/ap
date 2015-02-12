<?php

/**
 * Order_DataTables_Adapter_PaymentType
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_DataTables_Adapter_PaymentType extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('pt');
        $q->select('pt.*');
        return $q;
    }
}

