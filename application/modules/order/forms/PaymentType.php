<?php

/**
 * Order_Form_PaymentType
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_Form_PaymentType extends Admin_Form {
    
    public function init() {
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $name = $this->createElement('text', 'name');
        $name->setLabel('Name');
        $name->setRequired(true);
        $name->setDecorators(self::$textDecorators);
        $name->setAttrib('class', 'span8');
        
        $description = $this->createElement('textarea', 'description');
        $description->setLabel('Description');
        $description->setDecorators(self::$textareaDecorators);
        $description->setAttrib('class', 'tinymce uniform');
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
            $name,
            $description,
            $submit
        ));
    }
    
}

