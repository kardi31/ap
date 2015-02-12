<?php

/**
 * Location_DataTables_Category
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Location_DataTables_Category extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Location_DataTables_Adapter_Category';
    }
}

