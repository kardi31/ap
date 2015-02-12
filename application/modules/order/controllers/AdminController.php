<?php

/**
 * Order_AdminController
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_AdminController extends MF_Controller_Action {
    
    public function init() {
        $this->_helper->ajaxContext()
                ->addActionContext('change-item-vat', 'json')
                ->initContext();
        parent::init();
    }
    
    
    public function listOrderAction() {
        if($dashboardTime = $this->_helper->user->get('dashboard_time')) {
            if(isset($dashboardTime['new_orders'])) {
                $dashboardTime['new_orders'] = time();
                $this->_helper->user->set('dashboard_time', $dashboardTime);
            }
        }
    }
    
    public function listOrderDataAction() {
        $table = Doctrine_Core::getTable('Order_Model_Doctrine_Order');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Order_DataTables_Order', 
            'columns' => array('or.id', 'CONCAT_WS(" ", u.first_name, u.last_name)', 'or.total_cost', 'or.created_at'),
            'searchFields' => array('or.id', 'CONCAT_WS(" ", u.first_name, u.last_name)', 'dt.name', 'pt.name')
        ));
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();
            
            if ($result['OrderStatus']['name'] == 'nowe'):
                $row['DT_RowClass'] = 'info';
            endif;

            $row[] = $result['id'];
            $row[] = $result['User']['first_name'].' '.$result['User']['last_name'];
            $row[] = $result['total_cost'];
            $row[] = $result['created_at'];
            $row[] = $result['Delivery']['DeliveryType']['name'];
            $row[] = $result['Payment']['PaymentType']['name'];
            $row[] = $result['OrderStatus']['name'];
            if ($result['invoice_id']):
                $row[] = '<span class="icon16 icomoon-icon-checkbox-2"></span>';
            else:
                $row[] = '<span class="icon16 icomoon-icon-checkbox-unchecked-2"></span>';
            endif;

            $options = '<a href="' . $this->view->adminUrl('edit-order', 'order', array('id' => $result['id'])) . '" title ="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;';     
            $row[] = $options;
            $rows[] = $row;
        }

        $response = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $dataTables->getDisplayTotal(),
            "iTotalDisplayRecords" => $dataTables->getTotal(),
            "aaData" => $rows
        );
        $this->_helper->json($response);
    }
    
    public function editOrderAction() {
        $orderService = $this->_service->getService('Order_Service_Order');
        $orderStatusService = $this->_service->getService('Order_Service_OrderStatus');
        
        $translator = $this->_service->get('translate');
        
        if(!$order = $orderService->getFullOrder((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Order not found');
        }

        $form = $orderService->getOrderForm($order);
      
        $form->setAction($this->view->adminUrl('edit-order', 'order'));
        
        $form->getElement('order_status_id')->setMultiOptions($orderStatusService->getTargetOrderStatusSelectOptions(false, "status zamówienia"));
        $form->getElement('delivery_status_id')->setMultiOptions($orderStatusService->getTargetOrderStatusSelectOptions(false, "status przesyłki"));
        $form->getElement('delivery_status_id')->setValue($order->get('Delivery')->getStatusId());
        $form->getElement('payment_status_id')->setMultiOptions($orderStatusService->getTargetOrderStatusSelectOptions(false, "status płatności"));
        $form->getElement('payment_status_id')->setValue($order->get('Payment')->getStatusId());
        $form->getElement('delivery_cost')->setValue($order->get('Delivery')->getDeliveryCost());
        
        if ($order['Delivery']['delivery_type_id'] != 9){
            $form->removeElement('delivery_cost');
        }

        $productsWorth = $orderService->getProductsWorth($order);
       
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {                                   
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                   
                    $values = $form->getValues();  
                    
                    $mail = new Zend_Mail('UTF-8');
                    if($order['user_id'] != NULL): 
                        $mail->addTo($order['User']['email'], $order['User']['first_name'].' '.$order['User']['last_name']);
                    else:
                        $mail->addTo($order['contact_email'], $order['Delivery']['DeliveryAddress']['name']);
                    endif;
                    $mail->setReplyTo("info@slimtea.pl", 'System zamówień slimtea.pl');
                    
                    $oldOrderStatus = $order['order_status_id'];
                    if ($values['type'] == "order"){
                        $orderService->saveOrderFromArray($values); 
                        if ($oldOrderStatus != $values['order_status_id']):
                            $mail->setSubject($translator->translate('Zmiana statusu wysyłki.'));
                            if($values['order_status_id'] == 7 && $order->get('Delivery')->getDeliveryTypeId() != 8):
                                $orderService->sendConfirmationMailChangeOrderStatusForRealized($mail, $this->view);
                            endif;
                        endif;
                    }
                    
                    $oldDeliveryStatus = $order['Delivery']['status_id'];
                    if ($values['type'] == "delivery"){
                        $valuesDelivery['id'] = $order['Delivery']->getId();
                        $valuesDelivery['status_id'] = $values['delivery_status_id'];
                        $orderService->saveDeliveryFromArray($valuesDelivery); 
                        if ($oldDeliveryStatus != $values['delivery_status_id']):
                            $mail->setSubject($translator->translate('Zmiana statusu wysyłki.'));
                            if($values['delivery_status_id'] == 6):
                                $orderService->sendConfirmationMailChangeDeliveryStatusForWaitWithCollection($mail, $this->view);
                            endif;
                            if($values['delivery_status_id'] == 9):
                                $orderService->sendConfirmationMailChangeDeliveryStatusForSent($mail, $this->view);
                            endif;   
                        endif;
                    }
                    
                    $oldPaymentStatus = $order['Payment']['status_id'];
                    if ($values['type'] == "payment"){
                        $valuesPayment['id'] = $order['Payment']->getId();
                        $valuesPayment['status_id'] = $values['payment_status_id'];
                        $orderService->savePaymentFromArray($valuesPayment); 
                        if ($oldPaymentStatus != $values['payment_status_id']):
                            $mail->setSubject($translator->translate('Zmiana statusu płatności.'));
                            if($values['payment_status_id'] == 5):
                                $orderService->sendConfirmationMailChangePaymentStatus($mail, $this->view);
                            endif;
                        endif;
                    }
                    
                    if ($values['type'] == "delivery_cost" && $order['Delivery']['delivery_cost'] == NULL){
                        $valuesDeliveryCost['id'] = $order['Delivery']->getId();
                        $values['delivery_cost'] = str_replace(",",".", $values['delivery_cost']);
                        $valuesDeliveryCost['delivery_cost'] = $values['delivery_cost'];
                        $orderService->saveDeliveryFromArray($valuesDeliveryCost); 
                        $order->setTotalCost($order->getTotalCost()+(double)$values['delivery_cost']);
                        $order->save();
                        $valuesPayment['id'] = $order['Payment']->getId();
                        $valuesPayment['status_id'] = 3;
                        $orderService->savePaymentFromArray($valuesPayment); 
                        $mail->setSubject('Delivery cost has been set.');
                        if($order['Payment']['payment_type_id'] == 1):
                            $orderService->sendConfirmationMailDetermineDeliveryCostsPaymentTransfer($order, $this->view->language, $mail, $this->view);
                        elseif($order['Payment']['payment_type_id'] == 2):
                            $orderService->sendConfirmationMailDetermineDeliveryCostsPaymentPayU($order, $this->view->language, $mail, $this->view);
                        endif;
                    }

                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-order', 'order', array('id' => $order->getId())));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }    
       
        $this->view->assign('form', $form);

        $this->view->assign('productsWorth', $productsWorth);
        $this->view->assign('order', $order);
    }
    
    public function listOrderItemDataAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $table = Doctrine_Core::getTable('Order_Model_Doctrine_Item');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Order_DataTables_Item', 
            'columns' => array('pro.name'),
            'searchFields' => array('pro.name')
        ));
        
        $results = $dataTables->getResult();
        
                
        $language = $i18nService->getAdminLanguage();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();
            
            $row[] = $result['Product']->Translation[$language->getId()]->name;
            $row[] = $result['price'];
            $row[] = $result['number'];
            $row[] = $result['Discount']['amount_discount'];
            $row[] = $result['Product']['vat'];

            $rows[] = $row;
        }

        $response = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $dataTables->getDisplayTotal(),
            "iTotalDisplayRecords" => $results->count(),
            "aaData" => $rows
        );
        $this->_helper->json($response);
    }
    
    public function paymentOrderDataAction() {
        $table = Doctrine_Core::getTable('Order_Model_Doctrine_Payment');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Order_DataTables_Payment', 
        ));
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();

            $row[] = $result['PaymentType']['name'];
            $row[] = $result['Status']['name'];

            $rows[] = $row;
        }

        $response = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $dataTables->getDisplayTotal(),
            "iTotalDisplayRecords" => $results->count(),
            "aaData" => $rows
        );
        $this->_helper->json($response);
    }
    
    public function deliveryOrderDataAction() {
        $table = Doctrine_Core::getTable('Order_Model_Doctrine_Delivery');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Order_DataTables_Delivery', 
        ));
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();

            $row[] = $result['DeliveryType']['name'];
            $row[] = $result['Status']['name'];

            $rows[] = $row;
        }

        $response = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $dataTables->getDisplayTotal(),
            "iTotalDisplayRecords" => $results->count(),
            "aaData" => $rows
        );
        $this->_helper->json($response);
    }
    
    public function listDeliveryTypeAction() {
        
    }
    
    public function listDeliveryTypeDataAction() {
        $table = Doctrine_Core::getTable('Order_Model_Doctrine_DeliveryType');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Order_DataTables_DeliveryType', 
            'columns' => array('dt.name'),
            'searchFields' => array('dt.name')
        ));
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();

            $row[] = $result['name'];
            $row[] = $result['type'];
            $row[] = $result['price'];
            
            $options = '<a href="' . $this->view->adminUrl('edit-delivery-type', 'order', array('id' => $result['id'])) . '" title ="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;';     
           // $options .= '<a href="' . $this->view->adminUrl('remove-delivery-type', 'order', array('id' => $result['id'])) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
            $row[] = $options;
            $rows[] = $row;
        }

        $response = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $dataTables->getDisplayTotal(),
            "iTotalDisplayRecords" => $dataTables->getTotal(),
            "aaData" => $rows
        );
        $this->_helper->json($response);
    }
    
    public function addDeliveryTypeAction() {
        $deliveryTypeService = $this->_service->getService('Order_Service_DeliveryType');
        
        $translator = $this->_service->get('translate');
        
        $form = $deliveryTypeService->getDeliveryTypeForm();
        $form->getElement('type')->setMultiOptions($deliveryTypeService->getTargetTypeSelectOptions(true));
        
        $form->setAction($this->view->adminUrl('add-delivery-type', 'order'));
           
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();

                    $deliveryType = $deliveryTypeService->saveDeliveryTypeFromArray($values);

                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-delivery-type', 'order'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('form', $form);
    }
    
    public function editDeliveryTypeAction() {
        $deliveryTypeService = $this->_service->getService('Order_Service_DeliveryType');
        
        $translator = $this->_service->get('translate');
        
        if(!$deliveryType = $deliveryTypeService->getDeliveryType((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Delivery type not found');
        }
        
        $form = $deliveryTypeService->getDeliveryTypeForm($deliveryType);
        $form->getElement('type')->setMultiOptions($deliveryTypeService->getTargetTypeSelectOptions(true));
      
        $form->setAction($this->view->adminUrl('edit-delivery-type', 'order'));
       
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {                                   
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                   
                    $values = $form->getValues();  
                    
                    $deliveryTypeService->saveDeliveryTypeFromArray($values); 

                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-delivery-type', 'order'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }    
       
        $this->view->assign('form', $form);
        $this->view->assign('deliveryType', $deliveryType);
    } 
    
    public function removeDeliveryTypeAction() {
        $deliveryTypeService = $this->_service->getService('Order_Service_DeliveryType');

        if($deliveryType = $deliveryTypeService->getDeliveryType($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                
                $deliveryTypeService->removeDeliveryType($deliveryType);
                     
                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-delivery-type', 'order'));
            } catch(Exception $e) {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                $this->_service->get('log')->log($e->getMessage(), 4);
            }
        }      
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-delivery-type', 'order')); 
    }
    
    public function listPaymentTypeAction() {
        
    }
    
    public function listPaymentTypeDataAction() {
        $table = Doctrine_Core::getTable('Order_Model_Doctrine_PaymentType');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Order_DataTables_PaymentType', 
            'columns' => array('pt.name'),
            'searchFields' => array('pt.name')
        ));
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();

            $row[] = $result['name'];
            
            $options = '<a href="' . $this->view->adminUrl('edit-payment-type', 'order', array('id' => $result['id'])) . '" title ="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;';     
            $options .= '<a href="' . $this->view->adminUrl('remove-payment-type', 'order', array('id' => $result['id'])) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
            $row[] = $options;
            $rows[] = $row;
        }

        $response = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $dataTables->getDisplayTotal(),
            "iTotalDisplayRecords" => $dataTables->getTotal(),
            "aaData" => $rows
        );
        $this->_helper->json($response);
    }
    
    public function addPaymentTypeAction() {
        $paymentTypeService = $this->_service->getService('Order_Service_PaymentType');
        
        $translator = $this->_service->get('translate');
        
        $form = $paymentTypeService->getPaymentTypeForm();
        
        $form->setAction($this->view->adminUrl('add-payment-type', 'order'));
           
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();

                    $paymentType = $paymentTypeService->savePaymentTypeFromArray($values);

                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-payment-type', 'order'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        $this->view->assign('form', $form);
    }
    
    public function editPaymentTypeAction() {
        $paymentTypeService = $this->_service->getService('Order_Service_PaymentType');
        
        $translator = $this->_service->get('translate');
        
        if(!$paymentType = $paymentTypeService->getPaymentType((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Payment type not found');
        }
        
        $form = $paymentTypeService->getPaymentTypeForm($paymentType);
      
        $form->setAction($this->view->adminUrl('edit-payment-type', 'order'));
       
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {                                   
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                   
                    $values = $form->getValues();  
                    
                    $paymentTypeService->savePaymentTypeFromArray($values); 

                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-payment-type', 'order'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }    
       
        $this->view->assign('form', $form);
        $this->view->assign('paymentType', $paymentType);
    } 
    
    public function removePaymentTypeAction() {
        $paymentTypeService = $this->_service->getService('Order_Service_PaymentType');

        if($paymentType = $paymentTypeService->getPaymentType($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                
                $paymentTypeService->removePaymentType($paymentType);
                     
                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-payment-type', 'order'));
            } catch(Exception $e) {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                $this->_service->get('log')->log($e->getMessage(), 4);
            }
        }      
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-payment-type', 'order')); 
    }
    
    public function listOrderStatusAction() {
        
    }
    
    public function listOrderStatusDataAction() {
        $table = Doctrine_Core::getTable('Order_Model_Doctrine_OrderStatus');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Order_DataTables_OrderStatus', 
            'columns' => array('os.name'),
            'searchFields' => array('os.name')
        ));
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();

            $row[] = $result['name'];
            $row[] = $result['type'];
            
            $options = '<a href="' . $this->view->adminUrl('edit-order-status', 'order', array('id' => $result['id'])) . '" title ="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;';     
            //$options .= '<a href="' . $this->view->adminUrl('remove-order-status', 'order', array('id' => $result['id'])) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
            $row[] = $options;
            $rows[] = $row;
        }

        $response = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $dataTables->getDisplayTotal(),
            "iTotalDisplayRecords" => $dataTables->getTotal(),
            "aaData" => $rows
        );
        $this->_helper->json($response);
    }
    
    public function addOrderStatusAction() {
        $orderStatusService = $this->_service->getService('Order_Service_OrderStatus');
        
        $translator = $this->_service->get('translate');
        
        $form = $orderStatusService->getOrderStatusForm();
        
        $form->setAction($this->view->adminUrl('add-order-status', 'order'));
        $form->getElement('type')->setMultiOptions($orderStatusService->getTargetTypeOrderStatusSelectOptions(true));

           
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();

                    $orderStatus = $orderStatusService->saveOrderStatusFromArray($values);

                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-order-status', 'order'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        $this->view->assign('form', $form);
    }
    
    public function editOrderStatusAction() {
        $orderStatusService = $this->_service->getService('Order_Service_OrderStatus');
        
        $translator = $this->_service->get('translate');
        
        if(!$orderStatus = $orderStatusService->getOrderStatus((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Order status not found');
        }
        
        $form = $orderStatusService->getOrderStatusForm($orderStatus);
        $form->getElement('type')->setMultiOptions($orderStatusService->getTargetTypeOrderStatusSelectOptions(true));
      
        $form->setAction($this->view->adminUrl('edit-order-status', 'order'));
       
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {                                   
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                   
                    $values = $form->getValues();  
                    
                    $orderStatusService->saveOrderStatusFromArray($values); 

                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-order-status', 'order'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }    
       
        $this->view->assign('form', $form);
        $this->view->assign('orderStatus', $orderStatus);
    } 
    
    public function removeOrderStatusAction() {
        $orderStatusService = $this->_service->getService('Order_Service_OrderStatus');

        if($orderStatus = $orderStatusService->getOrderStatus($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                
                $orderStatusService->removeOrderStatus($orderStatus);
                     
                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-order-status', 'order'));
            } catch(Exception $e) {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                $this->_service->get('log')->log($e->getMessage(), 4);
            }
        }      
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-order-status', 'order')); 
    }

    public function pdfInvoiceAction() {
        require_once('tcpdf/tcpdf.php');
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $orderService = $this->_service->getService('Order_Service_Order');
        
        if(!$order = $orderService->getFullOrder((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Order not found');
        }
       // $code = 'F-'.MF_Text::timeFormat($order['created_at'], 'Ymd').'-'.$order['id'];
        
        $code = 'F/'.$order['id'];
        $saleDate = MF_Text::timeFormat($order['created_at'], 'd.m.Y');
        $invoiceDate = date('d.m.Y');
        
        $this->view->assign('sale_date', $saleDate);
        $this->view->assign('invoice_date', $invoiceDate);
        $this->view->assign('code', $code);
        $this->view->assign('order', $order);
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); 

        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetAutoPageBreak(1, 30);
        $pdf->SetFont('freesans');
        
        $htmlcontent = $this->view->render('admin/pdf-invoice.phtml');

        //$pdf->SetPrintHeader(false);
       // $pdf->SetPrintFooter(false);
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        
        
        $pdf->addPage();
        $pdf->writeHTML($htmlcontent, true, 0, true, 0);
        $pdf->lastPage();
        $pdf->Output();
        $pdf->Output($code . '.pdf', 'D');     
    }
    
    public function listCouponAction() {
        
    }
    
    public function listCouponDataAction() {
        $table = Doctrine_Core::getTable('Order_Model_Doctrine_Coupon');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Order_DataTables_Coupon', 
            'columns' => array('c.type, c.amount, c.valid'),
            'searchFields' => array('c.type, c.amount, c.valid')
        ));
        
        $translator = $this->_service->get('translate');
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();

            $row[] = $result['code'];
            $row[] = $translator->translate($result['type']);
            $row[] = $result['amount_coupon'];
            if (MF_Coupon::isValid($result)){
                $row[] = $translator->translate('yes');
            }
            else{
                $row[] = $translator->translate('no');
            }
            if ($result['used'] == 1){
                $row[] = $translator->translate('yes');
            }
            else{
                $row[] = $translator->translate('no');
            }
            if ($result['sent'] == 1){
                $row[] = $translator->translate('yes');
            }
            else{
                $row[] = $translator->translate('no');
            }
            
            $options = "";
            if ($result['sent'] == 0){
                $options .= '<a href="' . $this->view->adminUrl('send-coupon', 'order', array('id' => $result['id'])) . '" title="' . $this->view->translate('Send') . '"><span class="icon24 icon-arrow-up"></span></a>&nbsp;&nbsp;';
            }
            $options .= '<a href="' . $this->view->adminUrl('edit-coupon', 'order', array('id' => $result['id'])) . '" title ="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;';     
            $options .= '<a href="' . $this->view->adminUrl('remove-coupon', 'order', array('id' => $result['id'])) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
            $row[] = $options;
            $rows[] = $row;
        }

        $response = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $dataTables->getDisplayTotal(),
            "iTotalDisplayRecords" => $dataTables->getTotal(),
            "aaData" => $rows
        );
        $this->_helper->json($response);
    }
    
    public function addCouponAction() {
        $couponService = $this->_service->getService('Order_Service_Coupon');
        
        $translator = $this->_service->get('translate');
        
        $form = $couponService->getCouponForm();
        $form->getElement('type')->setMultiOptions($couponService->getTargetCouponTypeSelectOptions(true, $translator));
        
        $form->setAction($this->view->adminUrl('add-coupon', 'order'));
           
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();

                    $couponService->saveCouponsFromArray($values);

                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-coupon', 'order'));
                } catch(Exception $e) {
                    var_dump($e->getMessage()); exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        $this->view->assign('form', $form);
    }
    
    public function editCouponAction() {
        $couponService = $this->_service->getService('Order_Service_Coupon');
        
        $translator = $this->_service->get('translate');
        
        if(!$coupon = $couponService->getCoupon((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Coupon not found');
        }
        
        $form = $couponService->getCouponForm($coupon);
        $form->getElement('type')->setMultiOptions($couponService->getTargetCouponTypeSelectOptions(true, $translator));
        $form->removeElement('number_of_coupon');
        
        $form->setAction($this->view->adminUrl('edit-coupon', 'order'));
       
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {                                   
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                   
                    $values = $form->getValues();  
                    
                    $couponService->saveCouponFromArray($values); 

                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-coupon', 'order'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }    
       
        $this->view->assign('form', $form);
        $this->view->assign('coupon', $coupon);
    } 
    
    public function removeCouponAction() {
        $couponService = $this->_service->getService('Order_Service_Coupon');

        if($coupon = $couponService->getCoupon($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                
                $couponService->removeCoupon($coupon);
                     
                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-coupon', 'order'));
            } catch(Exception $e) {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                $this->_service->get('log')->log($e->getMessage(), 4);
            }
        }      
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-coupon', 'order')); 
    }
    
    public function sendCouponAction() {
        $couponService = $this->_service->getService('Order_Service_Coupon');
        $userService = $this->_service->getService('User_Service_User');
        
        $translator = $this->_service->get('translate');
        
        if(!$coupon = $couponService->getCoupon((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Coupon not found');
        }

        $form = new Order_Form_CouponSend();
        $form->getElement('user_id')->setMultiOptions($userService->getTargetClientSelectOptions(true));
           
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();

                    $mail = new Zend_Mail('UTF-8');
                    $mail->setSubject("Slimtea - kupon rabatowy");
                   
                    if ($values['user_id']){
                        if(!$user = $userService->getFullUser($values['user_id'], 'id', Doctrine_Core::HYDRATE_ARRAY_SHALLOW)) {
                            throw new Zend_Controller_Action_Exception('User not found');
                        }
                        $mail->addTo($user['email']);
                    }
                    else{
                        $mail->addTo($values['email']);
                    }
                    $mail->setBodyHtml($this->view->partial('coupon.phtml', array('coupon' => $coupon)));
                    $mail->send();
                    $coupon->setSent(1);
                    $coupon->save();
                    
                    $this->view->messages()->add($translator->translate('Coupon has been sent'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-coupon', 'order'));
                } catch(Exception $e) {
                    var_dump($e->getMessage()); exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        $this->view->assign('form', $form);
        $this->view->assign('coupon', $coupon);
    }
    
    public function changeItemVatAction() {
        $itemService = $this->_service->getService('Order_Service_Order');
        
        $itemId = $this->getRequest()->getParam('itemId');
        $vat = $this->getRequest()->getParam('vat');
         
        $item = $itemService->setVatItem($itemId, $vat);
        $newVat = $item->getVat();
        
        $this->view->assign('newVat', $newVat);
        $this->view->assign('status', "success");
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
    }
    
}

