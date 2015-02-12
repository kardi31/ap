<?php

/**
 * Order_Form_Payu_Sms
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_Form_Payu_Sms extends Order_Form_Payu {
    
    public function init() {
        parent::init();
        $this->getElement('amount')->setName('amount_netto');
        $this->getElement('submit')->setLabel('SMS');
    }
    
    public function setup($config) {
        parent::setup($config);
        if(array_key_exists('UrlPlatnosci_pl', $config)) {
            $this->setAction($config['UrlPlatnosci_pl'] . '/UTF/NewSMS');
        }
        
    }
    
}

