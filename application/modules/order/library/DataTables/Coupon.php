<?php

/**
 * Order_DataTables_Coupon
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_DataTables_Coupon extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Order_DataTables_Adapter_Coupon';
    }
}

