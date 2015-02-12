<?php

/**
 * Order_PayuController
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_PayuController extends MF_Controller_Action {
    
//    public function init() {
//        $this->view->assign('langParams', array());
//    }
//    
    public function successAction() {
        $payUService = $this->_service->getService('Order_Service_Payment_PayU');
        
        $options = $this->getInvokeArg('bootstrap')->getOptions();
        $config = $options['payu'];
        $config['PosId'] = $this->view->cms()->setting('payuPosId');
        $config['PostAuthKey'] = $this->view->cms()->setting('payuPosAuthKey');

        if($payment = $payUService->getPayment($this->getRequest()->getParam('session_id'))) {
            $this->view->assign('payment', $payment);
        }
        
        $this->_helper->actionStack('layout-shop', 'index', 'default');
    }
    
    public function errorAction() {
        $payUService = $this->_service->getService('Order_Service_Payment_PayU');

        if($payment = $payUService->getPayment($this->getRequest()->getParam('session_id'))) {
            $this->view->assign('error', $this->getRequest()->getParam('error'));
        }
        
        $this->_helper->actionStack('layout-shop', 'index', 'default');
    }
    
    public function statusAction() {
        $payUService = $this->_service->getService('Order_Service_Payment_PayU');
        
        if($this->getRequest()->isPost()) {
            if($payment = $payUService->getPayment($this->getRequest()->getParam('session_id'))) {
                $options = $this->getInvokeArg('bootstrap')->getOptions();
                $config = $options['payu'];
                
                // sig in
                $sig2 = md5( $this->view->cms()->setting('payuPosId') . $payment->getId() . $this->getRequest()->getParam('ts') . $this->view->cms()->setting('key2') );
                // sig out
                $sig1 = md5( $this->view->cms()->setting('payuPosId') . $payment->getId() . $payment->getTs() . $this->view->cms()->setting('key1') );
                /* walidacja sig */
                if($sig2 == $this->getRequest()->getParam('sig')) {
                    
                    /* odczytanie statusu transakcji */
                    $client = new Zend_Http_Client($config['Payment_get']);
                    $client->setMethod(Zend_Http_Client::POST);
                    $client->setParameterPost(array(
                        'pos_id' => $this->view->cms()->setting('payuPosId'),
                        'session_id' => $payment->getId(),
                        'ts' => $payment->getTs(),
                        'sig' => $sig1
                    ));
                    $response = $client->request();
                    $data = simplexml_load_string($response->getBody());
                    
                    $sig3 = md5 (   $this->view->cms()->setting('payuPosId') . 
                                    (int)$data->trans->session_id . 
                                    "" . 
                                    (int)$data->trans->status .
                                    (int)$data->trans->amount .
                                    (string)$data->trans->desc .
                                    (string)$data->trans->ts .
                                    $this->view->cms()->setting('key2') 
                                );
                    
                    if ($sig3 == ((string)$data->trans->sig)):
                        if((int)$data->trans->session_id && $payment = $payUService->getPayment((int)$data->trans->session_id)) {
                            // zmiana statusu transakcji
                            if($payment->getStatus() != ((int)$data->trans->status)) {
                                $payment->setStatus((int)$data->trans->status);
                                $payment->setErrorStatus(null);
                                $payment->save();
                                if ($payment->getStatus() == 99):
                                    $payment->setStatusId(5);
                                    $payment->save();
                                endif;
                            }
                            if($payment->getTransId() == NULL):
                                $payment->setTransId((int)$data->trans->id);
                                $payment->save();
                            endif;
                            if ($payment->getDesc() == NULL):
                                $payment->setDesc((string)$data->trans->desc);
                                $payment->save();
                            endif;
                        }
                    endif; 
                    
                    echo 'OK';
                }
            }
        }
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        
    }

    /**
     * Debug
     * wysyłanie żądania do systemu Platnosci.pl w celu uzyskania statusu transakcji 
     */
    public function test2Action() 
    {
//        $payUService = $this->_service->getService('Order_Service_Payment_PayU');
//
//        $this->_helper->viewRenderer->setNoRender();
//        $this->_helper->layout->disableLayout();
//
//        $options = $this->getInvokeArg('bootstrap')->getOptions();
//        $config = $options['payu'];
//        
//        $payment = $payUService->getPayment(14);
//            
//        $sig1 = md5( $this->view->cms()->setting('payuPosId') . $payment->getId() . $payment->getTs() . $this->view->cms()->setting('key1') );
//        
//        $client = new Zend_Http_Client($config['Payment_get']);
//        $client->setMethod(Zend_Http_Client::POST);
//        $client->setParameterPost(array(
//            'pos_id' => $this->view->cms()->setting('payuPosId'),
//            'session_id' => $payment->getId(),
//            'ts' => $payment->getTs(),
//            'sig' => $sig1
//        ));
//        $response = $client->request();
//        $data = simplexml_load_string($response->getBody());
//
//        $sig2 = md5 ( $this->view->cms()->setting('payuPosId') . 
//                      (int)$data->trans->session_id . 
//                      "" . 
//                      (int)$data->trans->status .
//                      (int)$data->trans->amount .
//                      (string)$data->trans->desc .
//                      (string)$data->trans->ts .
//                      $this->view->cms()->setting('key2') );
//
//        if ($sig2 == ((string)$data->trans->sig)):
//            if((int)$data->trans->session_id && $payment = $payUService->getPayment((int)$data->trans->session_id)) {
//                // zmiana statusu transakcji
//                if($payment->getStatus() != ((int)$data->trans->status)) {
//                    $payment->setStatus((int)$data->trans->status);
//                    $payment->setErrorStatus(null);
//                    $payment->save();
//                    if ($payment->getStatus() == 99):
//                        $payment->setStatusId(5);
//                        $payment->save();
//                    endif;
//                }
//                if($payment->getTransId() == NULL):
//                    $payment->setTransId((int)$data->trans->id);
//                    $payment->save();
//                endif;
//                if ($payment->getDesc() == NULL):
//                    $payment->setDesc((string)$data->trans->desc);
//                    $payment->save();
//                endif;
//            }
//        endif; 
//
//        echo 'OK';
    }
}

