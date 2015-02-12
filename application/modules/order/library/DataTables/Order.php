<?php

/**
 * Order_DataTables_Order
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_DataTables_Order extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Order_DataTables_Adapter_Order';
    }
}

