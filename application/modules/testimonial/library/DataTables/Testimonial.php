<?php

/**
 * Testimonial_DataTables_Testimonial
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Testimonial_DataTables_Testimonial extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Testimonial_DataTables_Adapter_Testimonial';
    }
}

