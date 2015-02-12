<?php

/**
 * Order_Service_Payment_PayU
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_Service_Payment_PayU extends Order_Service_Payment {
    
//    public function updatePayment(Order_Model_Doctrine_Payment $payment) {
//        $options = $this->getOptions();
//        
//        $timestamp = MF_Text::timeFormat($payment['created_at'], 'U');
//        
//        $sig1 = md5( $options['PosId'] . $payment->getId() . $timestamp . $options['Key1'] );
//        
//        $client = new Zend_Http_Client($options['Payment_get']);
//        $client->setMethod(Zend_Http_Client::POST);
//        $client->setParameterPost(array(
//            'pos_id' => $options['PosId'],
//            'session_id' => $payment->getId(),
//            'ts' => $timestamp,
//            'sig' => $sig1
//        ));
//        $response = $client->request();
//
//        $lines = explode(PHP_EOL, $response->getBody());
//
//        $params = array();
//        foreach($lines as $line) {
//            $pairs = explode(':', $line);
//            $key = isset($pairs[0]) ? $pairs[0] : '';
//            $value = isset($pairs[1]) ? $pairs[1] : '';
//            $params[$key] = $value;
//        }
//
//        if(array_key_exists('trans_session_id', $params)) {
//            // zmiana statusu transakcji
//            if($payment->getStatus() != $params['trans_status']) {
//                $payment->setStatus((int) $params['trans_status']);
//                $payment->setError(null);
//                $payment->save();
//                return true;
//            }
//        }
//    }
}

