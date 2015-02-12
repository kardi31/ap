<?php

/**
 * Testimonial_DataTables_Adapter_Testimonial
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Testimonial_DataTables_Adapter_Testimonial extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('t');
        $q->select('t.*');
        $q->addSelect('tt.*');
        $q->leftJoin('t.Translation tt');
        $q->andWhere('t.level > ?', 0);
        $q->addOrderBy('t.lft ASC');
        return $q;
    }
}

