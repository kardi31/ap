<?php

/**
 * Partner
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Affilateprogram_DataTables_Adapter_Partner extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('p');
        $q->select('p.*');
        return $q;
    }
}

