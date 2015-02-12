<?php

class Order_Form_DeliveryPayment extends Admin_Form
{
    public function init() {
        $deliveryTypeService = MF_Service_ServiceBroker::getInstance()->getService('Order_Service_DeliveryType');
        $deliveryTypes = $deliveryTypeService->getDeliveryTypes();
        $paymentTypeService = MF_Service_ServiceBroker::getInstance()->getService('Order_Service_PaymentType');
        $paymentTypes = $paymentTypeService->getPaymentTypes();
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $delivery = $this->createElement('radio', 'delivery_type_id');
        $delivery->setLabel('Sposób dostawy');
      //  $delivery->setRequired(true);
        $delivery->setDecorators(self::$textDecorators);
        $delivery->setAttrib('class', 'span8');
        $delivery->setRequired();
        foreach($deliveryTypes as $type):
            $delivery->addMultiOption($type['id'],$type['name']." - ".$type['price']." zł");
        endforeach;
        
        $payment = $this->createElement('radio', 'payment_type_id');
        $payment->setLabel('Sposób płatności');
       // $payment->setRequired(true);
        $payment->setDecorators(self::$textDecorators);
        $payment->setAttrib('class', 'span8');
        $payment->setRequired();
        foreach($paymentTypes as $type):
            $payment->addMultiOption($type['id'],$type['name']);
        endforeach;
  
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Make order');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $id,
            $delivery,
            $payment,
            $submit
        ));
    }
}