<?php

/**
 * Order_Form_DeliveryType
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_Form_DeliveryType extends Admin_Form {
    
    public function init() {
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $name = $this->createElement('text', 'name');
        $name->setLabel('Name');
        $name->setRequired(true);
        $name->setDecorators(self::$textDecorators);
        $name->setAttrib('class', 'span8');
        
        $type = $this->createElement('select', 'type');
        $type->setLabel('Type');
        $type->setRequired();
        $type->setDecorators(self::$selectDecorators);
        
        $price = $this->createElement('text', 'price');
        $price->setLabel('Price');
        $price->setRequired(true);
        $price->setDecorators(self::$textDecorators);
        $price->setAttrib('class', 'span8');
         
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
            $price,
            $description,
            $type,
            $submit
        ));
    }
    
}

