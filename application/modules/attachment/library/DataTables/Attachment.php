<?php

/**
 * Attachment_DataTables_Attachment
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Attachment_DataTables_Attachment extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Attachment_DataTables_Adapter_Attachment';
    }
}

