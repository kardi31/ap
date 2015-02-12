<?php

/**
 * Affilateprogram_Form_Commission
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Affilateprogram_Form_Commission extends Admin_Form {
    
    public function init() {
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
      
        $startDate = $this->createElement('text', 'start_date');
        $startDate->setLabel('From');
        $startDate->setRequired(true);
        $startDate->setDecorators(self::$datepickerDecorators);
        $startDate->setAttrib('class', 'span8');
        
        $endDate = $this->createElement('text', 'end_date');
        $endDate->setLabel('To');
        $endDate->setRequired(true);
        $endDate->setDecorators(self::$datepickerDecorators);
        $endDate->setAttrib('class', 'span8');
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Check');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
            $startDate,
            $endDate,
            $submit
        ));
    }
    
}

