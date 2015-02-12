<?php

/**
 * Order_Form_PaymentType
 *
 */
class Order_Form_DiscountCode extends Admin_Form {
    
    public function init() {
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $code = $this->createElement('text', 'code');
        $code->setLabel('Code');
        $code->setRequired(true);
        $code->setDecorators(self::$textDecorators);
        $code->setAttrib('class', 'span8');
        
        $discount = $this->createElement('text', 'discount');
        $discount->setLabel('Discount (%)');
        $discount->setDecorators(self::$textareaDecorators);
        $discount->setAttrib('class', 'span8');
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
            $code,
            $discount,
            $submit
        ));
    }
    
}

