<?php

/**
 * Order_Service_Order
 *
@author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_Service_Order extends MF_Service_ServiceAbstract {
    
    protected $orderTable;
    protected $itemTable;
    protected $deliveryTable;
    protected $deliveryAddressTable;
    protected $paymentTable;
    protected $invoiceTable;
    protected $cart;
    
    public function init() {
        $this->orderTable = Doctrine_Core::getTable('Order_Model_Doctrine_Order');
        $this->itemTable = Doctrine_Core::getTable('Order_Model_Doctrine_Item');
        $this->deliveryTable = Doctrine_Core::getTable('Order_Model_Doctrine_Delivery');
        $this->deliveryAddressTable = Doctrine_Core::getTable('Order_Model_Doctrine_DeliveryAddress');
        $this->paymentTable = Doctrine_Core::getTable('Order_Model_Doctrine_Payment');
        $this->invoiceTable = Doctrine_Core::getTable('Order_Model_Doctrine_Invoice');
        parent::init();
    }
    
    public function getItem($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->itemTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getOrder($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->orderTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getDelivery($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->deliveryTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getPayment($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->paymentTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getDeliveryAddress($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->deliveryAddressTable->findOneBy($field, $id, $hydrationMode);
    }

    public function getProductsWorth(Order_Model_Doctrine_Order $order)
    {
        $sum = 0;
        foreach($order['Items'] as $item):
            $sum += $item['price']*$item['number'];
        endforeach;

        return $sum;
    }

    public function getOrderForm(Order_Model_Doctrine_Order $order = null) {
        $form = new Order_Form_Order();
        if(null != $order) { 
            $form->populate($order->toArray());
        }
        return $form;
    }
    
    public function saveOrderFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$order = $this->getOrder((int) $values['id'])) {
            $order = $this->orderTable->getRecord();
        }
        $order->fromArray($values);
        $order->save();
        
        return $order;
    }
    
    public function saveDeliveryFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }

        if(!$delivery = $this->getDelivery((int) $values['id'])) {
            $delivery = $this->deliveryTable->getRecord();
        }
        $delivery->fromArray($values);
        $delivery->save();
        
        return $delivery;
    }
    
    public function saveDeliveryAddressFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$deliveryAddress = $this->getDeliveryAddress((int) $values['id'])) {
            $deliveryAddress = $this->deliveryAddressTable->getRecord();
        }
        $deliveryAddress->fromArray($values);
        $deliveryAddress->save();
        
        return $deliveryAddress;
    }
    
    public function saveItemFromArray($values) {
        $item = $this->itemTable->getRecord();
        $item->fromArray($values);
        $item->save();
        
        return $item;
    }
    
    public function savePaymentFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$payment = $this->getPayment((int) $values['id'])) {
            $payment = $this->paymentTable->getRecord();
        }
        $payment->fromArray($values);
        $payment->save();
        
        return $payment;
    }
    
    public function saveInvoiceFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$invoice = $this->getPayment((int) $values['id'])) {
            $invoice = $this->invoiceTable->getRecord();
        }
        $invoice->fromArray($values);
        $invoice->save();
        
        return $invoice;
    }
     
    public function getFullOrder($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->orderTable->getFullOrderQuery();
        $q->andWhere('o.' . $field . ' = ?', $id);
        return $q->fetchOne(array(), $hydrationMode);
    }
    
    public function getNewOrders($date, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->orderTable->getFullOrderQuery();
        $q->andWhere('o.created_at > ?', $date);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getAllOrders($countOnly = false) {
        if(true == $countOnly) {
            return $this->orderTable->count();
        } else {
            return $this->orderTable->findAll();
        }
    }
    
    public function getOrderItemsIds($orderId, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->itemTable->getOrderItemsIdsQuery();
        $q->andWhere('i.order_id = ?', $orderId);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getCart() {
        if(!$this->cart) {
            $this->cart = new Order_Model_Cart();
        }
        return $this->cart;
    }
    
    public function getUserOrders($userId, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->orderTable->getFullOrderQuery();
        $q->leftJoin('o.Payment p');
        $q->leftJoin('p.Status ps');
        $q->leftJoin('d.Status dels');
        $q->addSelect('p.*,ps.*,dels.*');
        $q->andWhere('u.id = ?', $userId);
        $q->addOrderBy('o.created_at DESC');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function sendConfirmationWithOrderToUs($user, Order_Model_Doctrine_Order $order, Zend_Mail $mail, Zend_View_Interface $view, $partial = 'email/confirmation-order-to-us.phtml') {                   
        $mail->setBodyHtml(
            $view->partial($partial, array('order' => $order, 'user' => $user))
        );
        $mail->send(); 
    }
    
    public function sendConfirmationMailWithPaymentByTransferPayment(Zend_Mail $mail, Order_Model_Doctrine_Order $order, $language, Zend_View_Interface $view, $partial = 'email/confirmation-order-payment-by-transfer.phtml') {                   
        $mail->setBodyHtml(
            $view->partial($partial, array('order' => $order, 'language' => $language))
        );
        $mail->send(); 
    }
    
    public function sendConfirmationMailWithPaymentByPayU(Zend_Mail $mail, Order_Model_Doctrine_Order $order, $language, Zend_View_Interface $view, $partial = 'email/confirmation-order-payment-by-payu.phtml') {                   
        $mail->setBodyHtml(
            $view->partial($partial, array('order' => $order, 'language' => $language))
        );
        $mail->send(); 
    }
    
    
    public function sendConfirmationMailWithPaymentByCash(Zend_Mail $mail, Order_Model_Doctrine_Order $order, $language,  Zend_View_Interface $view, $partial = 'email/confirmation-order-payment-by-cash.phtml') {                   
        $mail->setBodyHtml(
            $view->partial($partial, array('order' => $order, 'language' => $language))
        );
        $mail->send(); 
    }
    
    public function sendConfirmationMailWithDeliveryAbroad(Zend_Mail $mail, Order_Model_Doctrine_Order $order, $language,  Zend_View_Interface $view, $partial = 'email/confirmation-order-delivery-abroad.phtml') {                   
        $mail->setBodyHtml(
            $view->partial($partial, array('order' => $order, 'language' => $language))
        );
        $mail->send(); 
    }

    public function sendConfirmationMailChangeOrderStatusForRealized(Zend_Mail $mail, Zend_View_Interface $view, $partial = 'email/confirmation-change-order-status-for-realized.phtml') {                   
        $mail->setBodyHtml(
            $view->partial($partial)
        );
        $mail->send(); 
    }
    
    public function sendConfirmationMailChangeDeliveryStatusForSent(Zend_Mail $mail, Zend_View_Interface $view, $partial = 'email/confirmation-change-delivery-status-for-sent.phtml') {                   
        $mail->setBodyHtml(
            $view->partial($partial)
        );
        $mail->send(); 
    }
    
    
    
    public function sendConfirmationMailChangeDeliveryStatusForWaitWithCollection(Zend_Mail $mail, Zend_View_Interface $view, $partial = 'email/confirmation-change-delivery-status-for-wait-with-collection.phtml') {                   
        $mail->setBodyHtml(
            $view->partial($partial)
        );
        $mail->send(); 
    }
    
    public function sendConfirmationMailChangePaymentStatus(Zend_Mail $mail, Zend_View_Interface $view, $partial = 'email/confirmation-change-payment-status.phtml') {                   
        $mail->setBodyHtml(
            $view->partial($partial)
        );
        $mail->send(); 
    }
    
    public function sendConfirmationMailWithPaymentByTransferWithCollection(Zend_Mail $mail, Order_Model_Doctrine_Order $order, $language, Zend_View_Interface $view, $partial = 'email/confirmation-order-payment-by-transfer-with-collection.phtml') {                   
        $mail->setBodyHtml(
            $view->partial($partial, array('order' => $order, 'language' => $language))
        );
        $mail->send(); 
    }
    
    public function sendConfirmationMailWithPaymentByCourierCollection(Zend_Mail $mail, Order_Model_Doctrine_Order $order, $language, Zend_View_Interface $view, $partial = 'email/confirmation-order-payment-by-courier-with-collection.phtml') {                   
        $mail->setBodyHtml(
            $view->partial($partial, array('order' => $order, 'language' => $language))
        );
        $mail->send(); 
    }
    
    public function sendConfirmationMailDetermineDeliveryCostsPaymentTransfer(Order_Model_Doctrine_Order $order, $language, Zend_Mail $mail, Zend_View_Interface $view, $partial = 'email/confirmation-determine-delivery-costs-transfer.phtml') {                   
        $mail->setBodyHtml(
            $view->partial($partial, array('order' => $order, 'language' => $language))
        );
        $mail->send(); 
    }
    
    public function sendConfirmationMailDetermineDeliveryCostsPaymentPayU(Order_Model_Doctrine_Order $order, $language, Zend_Mail $mail, Zend_View_Interface $view, $partial = 'email/confirmation-determine-delivery-costs-payu.phtml') {                   
        $mail->setBodyHtml(
            $view->partial($partial, array('order' => $order, 'language' => $language))
        );
        $mail->send(); 
    }
    
    
    public function setVatItem($itemId, $vat){
        $item = $this->getItem($itemId);
        $item->setVat((double)$vat);
        $item->save();
        return $item;
    }
}
?>