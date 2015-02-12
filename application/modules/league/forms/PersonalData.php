<?php

class Order_Form_PersonalData extends Admin_Form
{
    public function init() {
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
         $date = $this->createElement('text', 'date');
         $date->setDecorators(self::$textDecorators);
         $date->setAttrib('class', 'span8 date');
        
        for($j=1;$j<5;$j++):
            ${'time'.$j} = $this->createElement('text', 'team'.$j);
            ${'time'.$j}->setDecorators(self::$textDecorators);
            ${'time'.$j}->setAttrib('class', 'span8 time');
            
            $this->addElement(${'time'.$j});
        endfor;
        
        
        for($i=1;$i<=10;$i++):
            ${'team'.$i} = $this->createElement('select', 'team'.$i);
            ${'team'.$i}->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
            ${'team'.$i}->setAttrib('class', 'span8');
            
            $this->addElement(${'team'.$j});
        endfor;
        
        
        

        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            
            $submit
        ));
    }
}