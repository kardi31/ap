<?php

/**
 * Order_Form_Payu
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_Form_Payu extends Zend_Form {
    
    public function init() {
        $posId = $this->createElement('hidden', 'pos_id');
        $posId->setDecorators(array('ViewHelper'));
        $posAuthKey = $this->createElement('hidden', 'pos_auth_key');
        $posAuthKey->setDecorators(array('ViewHelper'));
        $sessionId = $this->createElement('hidden', 'session_id');
        $sessionId->setDecorators(array('ViewHelper'));
        $amount = $this->createElement('hidden', 'amount');
        $amount->setDecorators(array('ViewHelper'));
        $desc = $this->createElement('hidden', 'desc');
        $desc->setDecorators(array('ViewHelper'));
        
        $firstName = $this->createElement('hidden', 'first_name');
        $firstName->setDecorators(array('ViewHelper'));
        $lastName = $this->createElement('hidden', 'last_name');
        $lastName->setDecorators(array('ViewHelper'));
        $email = $this->createElement('hidden', 'email');
        $email->setDecorators(array('ViewHelper'));
        
        $clientIp = $this->createElement('hidden', 'client_ip');
        $clientIp->setDecorators(array('ViewHelper'));
        
        $ts = $this->createElement('hidden', 'ts');
        $ts->setDecorators(array('ViewHelper'));
        
        $sig = $this->createElement('hidden', 'sig');
        $sig->setDecorators(array('ViewHelper'));
        
//        $js = $this->createElement('hidden', 'js');
//        $js->setDecorators(array('ViewHelper'));
//        $js->setValue(0);
        $submit = $this->createElement('submit', 'submit');
        $submit->setLabel('Pay');
        $submit->setDecorators(array('ViewHelper'));
        
        $this->setDecorators(array(
            'FormElements',
            'Form'
        ));
        
        $this->addElements(array(
            $posId, $posAuthKey, $sessionId, $amount, $desc, $firstName, $lastName, $email, $clientIp, $ts, $sig, $submit
        ));
    }
    
    public function setup($config) {
        if(array_key_exists('UrlPlatnosci_pl', $config)) {
            $this->setAction($config['UrlPlatnosci_pl'] . '/UTF/NewPayment');
        }
        if(array_key_exists('PosId', $config)) {
            $this->pos_id->setValue($config['PosId']);
        }
        if(array_key_exists('PosAuthKey', $config)) {
            $this->pos_auth_key->setValue($config['PosAuthKey']);
        }
        
        $this->setName('payform');
        $this->setMethod('post');
    }
}

