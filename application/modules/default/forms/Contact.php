<?php

/**
 * Contact
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Default_Form_Contact extends Admin_Form {
    
   
    public function init() {
        $name = $this->createElement('text', 'name');
        $name->setLabel('First name');
        $name->setRequired(true);
        $name->setDecorators(self::$textDecorators);
        $name->setAttrib('class', 'span8');
        
        $surname = $this->createElement('text', 'surname');
        $surname->setLabel('Last name');
        $surname->setRequired(true);
        $surname->setDecorators(self::$textDecorators);
        $surname->setAttrib('class', 'span8');
        
        
        $email = new Glitch_Form_Element_Text_Email('email');
        $email->setLabel('Email');
        $email->setRequired(true);
        $email->setDecorators(self::$textDecorators);
        $email->setAttrib('class', 'span8');
        
        
        $phone = $this->createElement('text','phone');
        $phone->setLabel('Phone');
        $phone->setDecorators(self::$textDecorators);
        $phone->setAttrib('class', 'span8');
        
        
        $subject = $this->createElement('text', 'subject');
        $subject->setLabel('Title');
        $subject->setRequired(true);
        $subject->setDecorators(self::$textDecorators);
        $subject->setAttrib('class', 'span8');
        
        
        $message = $this->createElement('textarea', 'messageContact');
        $message->setLabel('Message');
        $message->setRequired(true);
        $message->setDecorators(self::$textDecorators);
        $message->setAttrib('class', 'span8');
        
          
       $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Submit');
        $submit->setDecorators(self::$submitDecorators);
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
       
        $this->setElements(array(
            $name,
            $surname,
            $email,
            $phone,
            $subject,
            $message,
            $submit
        ));
    }
}

