<?php

/**
 * Order_Form_Payu_Payment
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_Form_Payu_Payment extends Order_Form_Payu {
    
    public function init() {
        parent::init();
        $this->getElement('submit')->setLabel('Zapłać teraz');
    }
    
    public function setup($config) {
        parent::setup($config);
        if(array_key_exists('UrlPlatnosci_pl', $config)) {
            $this->setAction($config['UrlPlatnosci_pl'] . '/UTF/NewPayment');
        }
    }
    
}

