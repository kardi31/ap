<?php

/**
 * Order_DataTables_DeliveryType
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_DataTables_DeliveryType extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Order_DataTables_Adapter_DeliveryType';
    }
}

