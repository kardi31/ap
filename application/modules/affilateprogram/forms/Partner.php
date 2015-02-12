<?php

/**
 * Affilateprogram_Form_Partner
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Affilateprogram_Form_Partner extends Admin_Form {
    
    public function init() {
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
      
        $name = $this->createElement('text', 'name');
        $name->setLabel('Name');
        $name->setRequired(true);
        $name->setDecorators(self::$textDecorators);
        $name->setAttrib('class', 'span8');
        
        $discount = $this->createElement('text', 'discount');
        $discount->setLabel('Discount in %');
        $discount->setRequired(true);
        $discount->setDecorators(self::$textDecorators);
        $discount->setAttrib('class', 'span8');
        
        $comission = $this->createElement('text', 'comission');
        $comission->setLabel('Comission in %');
        $comission->setRequired(true);
        $comission->setDecorators(self::$textDecorators);
        $comission->setAttrib('class', 'span8');
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
            $name,
            $discount,
            $comission,
            $submit
        ));
    }
    
}

