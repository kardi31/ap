<?php

/**
 * Order_Form_OrderStatus
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Tournament_Form_GroupTeam extends Admin_Form {
    
    public function init() {
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $name = $this->createElement('text', 'name');
        $name->setLabel('Name');
        $name->setRequired(true);
        $name->setDecorators(self::$textDecorators);
        $name->setAttrib('class', 'span8');
        
        $group_id = $this->createElement('select', 'group_id');
        $group_id->setLabel('Grupa');
        $group_id->setRequired(false);
        $group_id->setDecorators(self::$selectDecorators);
        $group_id->setAttrib('class', 'span8');
        for($i=1;$i<10;$i++):
        $group_id->addMultiOption($i,'Groupa '.$i);
        endfor;
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));
        
        $this->setElements(array(
            $id,
            $name,
            $group_id,
            $submit
        ));
    }
    
}

