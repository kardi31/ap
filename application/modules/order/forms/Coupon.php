<?php

/**
 * Order_Form_Coupon
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_Form_Coupon extends Admin_Form {
    
    public function init() {
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $numberofCoupon = $this->createElement('text', 'number_of_coupon');
        $numberofCoupon->setLabel('Number of coupon');
        $numberofCoupon->setRequired(true);
        $numberofCoupon->setDecorators(self::$textDecorators);
        $numberofCoupon->setAttrib('class', 'span8');
        
        $type = $this->createElement('select', 'type');
        $type->setLabel('Type');
        $type->setRequired();
        $type->setDecorators(self::$selectDecorators);
        
        $amountCoupon = $this->createElement('text', 'amount_coupon');
        $amountCoupon->setLabel('Amount');
        $amountCoupon->setRequired(true);
        $amountCoupon->setDecorators(self::$textDecorators);
        $amountCoupon->setAttrib('class', 'span8');
        
        $startValidityDate = $this->createElement('text', 'start_validity_date');
        $startValidityDate->setLabel('Start date');
        $startValidityDate->setRequired(true);
        $startValidityDate->setDecorators(self::$datepickerDecorators);
        $startValidityDate->setAttrib('class', 'span8');
        
        $finishValidityDate = $this->createElement('text', 'finish_validity_date');
        $finishValidityDate->setLabel('Finish date');
        $finishValidityDate->setRequired(true);
        $finishValidityDate->setDecorators(self::$datepickerDecorators);
        $finishValidityDate->setAttrib('class', 'span8');
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
            $numberofCoupon,
            $type,
            $amountCoupon,
            $startValidityDate,
            $finishValidityDate,
            $submit
        ));
    }
    
}

