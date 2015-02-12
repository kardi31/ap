<?php

/**
 * Order_Form_DiscountCode
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_Form_DiscountCode extends Admin_Form {
    
    public function init() {
        
        $code = $this->createElement('text', 'discount_code');
        $code->setRequired(true);
        $code->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $code->setAttrib('class', 'form-control');

        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Apply coupon');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-default', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
            $code,
            $submit
        ));
    }
    
}

