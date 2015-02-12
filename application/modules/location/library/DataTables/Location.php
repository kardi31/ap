<?php

/**
 * Location
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Location_DataTables_Location extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Location_DataTables_Adapter_Location';
    }
}

