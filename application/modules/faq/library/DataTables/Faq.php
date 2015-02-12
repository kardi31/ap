<?php

/**
 * Faq
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Faq_DataTables_Faq extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Faq_DataTables_Adapter_Faq';
    }
}

