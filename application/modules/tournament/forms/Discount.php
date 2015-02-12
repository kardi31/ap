<?php

class Order_Form_Discount extends Admin_Form
{
    public function init() {
       
      
        
        $code = $this->createElement('text', 'code');
        $code->setLabel('Kod rabatowy');
        $code->setRequired()
              -> removeDecorator('HtmlTag')
              -> removeDecorator('Label');
        $code->addValidators(array(
            array('alnum', false, array('allowWhiteSpace' => true))
        ));
        $code->addFilters(array(
            array('alnum', array('allowWhiteSpace' => true))
        ));
        $code->setValue('Wprowdź kod rabatowy');
        $code->setAttrib('onfocus','this.value = ""');
   //     $code->setAttrib('onblur','this.value = "Wprowdź kod rabatowy"');
       
       
  
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Add bonus code')
               -> removeDecorator('DtDdWrapper');
       
        $submit->setAttrib('type', 'submit');
     

        $this->setElements(array(
          
            $code,
            $submit
        ));
    }
}