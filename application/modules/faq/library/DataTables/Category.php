<?php

/**
 * Faq_DataTables_Category
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Faq_DataTables_Category extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Faq_DataTables_Adapter_Category';
    }
}

