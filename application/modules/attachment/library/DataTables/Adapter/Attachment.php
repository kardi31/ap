<?php

/**
 * Attachment_DataTables_Adapter_Attachment
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Attachment_DataTables_Adapter_Attachment extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('a');
        $q->select('a.*');
        return $q;
    }
    
}

