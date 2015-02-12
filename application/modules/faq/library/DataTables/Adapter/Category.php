<?php

/**
 * Category
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Faq_DataTables_Adapter_Category extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('x');
        $q->select('x.*');
        $q->addSelect('xt.*');
        $q->leftJoin('x.Translation xt');
        return $q;
    }
}

