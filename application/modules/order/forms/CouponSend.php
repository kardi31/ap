<?php

/**
 * Order_Form_CouponSend
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_Form_CouponSend extends Admin_Form {
    
    public function init() {
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $email = $this->createElement('text', 'email');
        $email->setLabel('Email');
        $email->setRequired(false);
        $email->setDecorators(self::$textDecorators);
        $email->setAttrib('class', 'span8');
        
        $userId = $this->createElement('select', 'user_id');
        $userId->setLabel('User');
        $userId->setRequired(false);
        $userId->setDecorators(self::$selectDecorators);
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Send');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
            $email,
            $userId,
            $submit
        ));
    }
    
}

