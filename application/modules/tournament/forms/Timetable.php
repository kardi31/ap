<?php

class Tournament_Form_Timetable extends Admin_Form
{
    public function init() {
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
         $date = $this->createElement('text', 'date');
         $date->setDecorators(self::$textDecorators);
         $date->setAttrib('class', 'span8 mask_date2');
    
        for($j=1;$j<=20;$j++):
            ${'time'.$j} = $this->createElement('text', 'time'.$j);
            ${'time'.$j}->setDecorators(self::$textDecorators);
            ${'time'.$j}->setAttrib('class', 'span8 mask_time2');
            $this->addElement(${'time'.$j});
            
            ${'group_round'.$j} = $this->createElement('checkbox', 'group_round'.$j);
            ${'group_round'.$j}->setDecorators(self::$textDecorators);
            ${'group_round'.$j}->setAttrib('class', 'span8');
            ${'group_round'.$j}->setValue(1);
            $this->addElement(${'group_round'.$j});
        endfor;
        
        
        for($i=1;$i<=40;$i++):
            ${'team'.$i} = $this->createElement('select', 'team'.$i);
            ${'team'.$i}->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
            ${'team'.$i}->setAttrib('class', 'span2');
            ${'team'.$i}->addMultiOption('', '');
            $this->addElement(${'team'.$i});
        endfor;
        

        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->addElement($date);
        $this->addElement($submit);
    }
}