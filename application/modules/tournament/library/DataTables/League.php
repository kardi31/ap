<?php

/**
 * Gallery
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Tournament_DataTables_Tournament extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Tournament_DataTables_Adapter_Tournament';
    }
}

