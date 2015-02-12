<?php

/**
 * Gallery
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Tournament_DataTables_Adapter_Tournament extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('l');
        $q->addSelect('l.*');
        return $q;
    }
}
