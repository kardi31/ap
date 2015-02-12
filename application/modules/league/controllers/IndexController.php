<?php

/**
 * Order_IndexController
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class League_IndexController extends MF_Controller_Action {
 
    public function indexAction() {

       
        $orderService = $this->_service->getService('Order_Service_Order');
     
        $modelCart = $orderService->getCart();
    
   
    }
    
    public function timetableAction()
    {
        
        $matchService = $this->_service->getService('League_Service_Match');
        $leagueService = $this->_service->getService('League_Service_League');
        if(!$league = $leagueService->getLeague($this->getRequest()->getParam('league'),'slug')){
            throw new Zend_Exception('League not found');
        }
        $data = $matchService->getTimetableDate($league->id);
       
        $timetable = $matchService->getTimetable($league->id);
        $this->view->assign('data',$data->toArray());
        $this->view->assign('timetable',$timetable);
         $this->view->assign('league',$league);
         
        
         $this->_helper->actionStack('layout','index','default');
    }
    
    public function showResultAction()
    {
        $leagueService = $this->_service->getService('League_Service_League');
        $matchService = $this->_service->getService('League_Service_Match');
        $shooterService = $this->_service->getService('League_Service_Shooter');
        if(!$league = $leagueService->getLeague($this->getRequest()->getParam('league'),'slug')){
            throw new Zend_Exception('League not found');
        }
       
        $results = $matchService->getResults($league->id);
        $this->view->assign('results',$results);
         $this->view->assign('league',$league);
        $this->view->assign('shooterService',$shooterService);
         $this->_helper->actionStack('layout','index','default');
    }
    
    public function showTableAction()
    {
        $leagueService = $this->_service->getService('League_Service_League');
        $matchService = $this->_service->getService('League_Service_Match');
        if(!$league = $leagueService->getLeague($this->getRequest()->getParam('league'),'slug')){
            throw new Zend_Exception('League not found');
        }
        $results = $matchService->getTable($league->id);
        $this->view->assign('tabela',$results);
         $this->view->assign('league',$league);
         $this->_helper->actionStack('layout','index','default');
    }

    public function showShootersAction()
    {
        $leagueService = $this->_service->getService('League_Service_League');
        $shooterService = $this->_service->getService('League_Service_Shooter');
        if(!$league = $leagueService->getLeague($this->getRequest()->getParam('league'),'slug')){
            throw new Zend_Exception('League not found');
        }
        $results = $shooterService->getShooters($league->id);
        $this->view->assign('strzelcy',$results);
         $this->view->assign('league',$league);
         $this->_helper->actionStack('layout','index','default');
    }
    
    public function showBookingAction()
    {
        $leagueService = $this->_service->getService('League_Service_League');
        $bookingService = $this->_service->getService('League_Service_Booking');
        if(!$league = $leagueService->getLeague($this->getRequest()->getParam('league'),'slug')){
            throw new Zend_Exception('League not found');
        }
        $yellowCards = $bookingService->getBooking($league->id,1);
        $redCards = $bookingService->getBooking($league->id,2);
        $this->view->assign('redCards',$redCards);
        $this->view->assign('yellowCards',$yellowCards);
         $this->view->assign('league',$league);
         $this->_helper->actionStack('layout','index','default');
    }

    public function showSquadAction()
    {
         $leagueService = $this->_service->getService('League_Service_League');
        $teamService = $this->_service->getService('League_Service_Team');
        if(!$league = $leagueService->getLeague($this->getRequest()->getParam('league'),'slug')){
            throw new Zend_Exception('League not found');
        }
        $team = $teamService->getTeam($this->getRequest()->getParam('team'),'slug');
         $this->view->assign('team',$team);
         $this->_helper->actionStack('layout','index','default');
    }
    
     public function showCupAction() {
       
        $photoDimensionService = $this->_service->getService('Default_Service_PhotoDimension');
        $cupService = $this->_service->getService('League_Service_Cup');
        $metatagService = $this->_service->getService('Default_Service_Metatag');

        if(!$cup = $cupService->getCup($this->getRequest()->getParam('slug'), 'slug', Doctrine_Core::HYDRATE_RECORD)) {
            throw new Zend_Controller_Action_Exception('Cup not found');
        }
        $photoDimension = $photoDimensionService->getElementDimension('page');
        
        $metatagService->setViewMetatags($cup['metatag_id'], $this->view);
        
        $this->_helper->actionStack('layout', 'index', 'default');
        $this->view->assign('cup', $cup);
        $this->view->assign('photoDimension', $photoDimension);
    }
    
    public function basketAction() {
        $this->_helper->layout->setLayout('login');
        $orderService = $this->_service->getService('Order_Service_Order');
        $modelCart = $orderService->getCart();
        $items = $modelCart->getItems();

        // liczenie wartości przedmiotów w koszyku i zapisywanie do sesji
        $itemsValue = $modelCart->countItemsValue($items);
        $modelCart->updatePrice('total_price',$itemsValue);
        

        $form = new Order_Form_DeliveryPayment();


         if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    $modelCart->updatePrice('delivery_type_id',$values['delivery_type_id']);
                    $modelCart->updatePrice('payment_type_id',$values['payment_type_id']);
                    $this->_helper->redirector->gotoUrl($this->view->url(array('action' => 'first-step'),'domain-order'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
         }}
         // jeżeli nie jest wprowadzony kod rabatowy to cena po rabacie jest ceną produktów
         else{
              $modelCart->updatePrice('total_price_after_discount',$modelCart->getPrice('total_price'));
         }
            $prices = $modelCart->getPrices(); 
        $this->view->assign('prices',$prices);
        $this->view->assign('form',$form);
        $this->view->assign('items',$items);
        $this->_helper->actionStack('layout', 'index', 'default');
       // $modelCart->clean();
    }
    public function displayDimensionAction()
    {
        
    }
    public function removeItemAction()
    {
        $id = $this->getRequest()->getParam('id');
        $orderService = $this->_service->getService('Order_Service_Order');
        $modelCart = $orderService->getCart();
        
        $modelCart->remove('Product_Model_Doctrine_Product',$id);
        
        $this->_helper->viewRenderer->setNoRender();
        return $this->redirect($this->view->url(array('action' => 'basket'),'domain-order'));
    }
    public function firstStepAction()
    {
        $this->_helper->layout->setLayout('login');
        $orderService = $this->_service->getService('Order_Service_Order');
        $userService = $this->_service->getService('User_Service_User');
        $ud = $userService->getFullUser(Zend_Auth::getInstance()->getIdentity(),'email');
        if($ud)
        {
            $user_id = $ud->getId();
            $userData = $userService->getProfile($user_id);
        }
        else
        {
            $userData = null;
        }
        $modelCart = $orderService->getCart();
        $form = new Order_Form_PersonalData();
        $clientType = $form->getElement('client_type');
        $form->removeElement('client_type');
        $form->removeElement('difAddress');
 //       Zend_Debug::dump($modelCart->getItems());
         if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    $modelCart->storeValues($values);
                 
                    $this->_helper->redirector->gotoUrl($this->view->url(array('action' => 'second-step'),'domain-order'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
             //   
         }}
       if($dataValues = $modelCart->getValues())
          $this->view->assign('dataValues',$dataValues);
         $this->_helper->actionStack('layout', 'index', 'default');
        $this->view->assign('form',$form);
        $this->view->assign('clientType',$clientType);
        $this->view->assign('userData',$userData);
    }
    public function secondStepAction()
    {
        $this->_helper->layout->setLayout('login');
        $orderService = $this->_service->getService('Order_Service_Order');
        $deliveryTypeService = $this->_service->getService('Order_Service_DeliveryType');
        $paymentTypeService = $this->_service->getService('Order_Service_PaymentType');
        $discountCodeService = $this->_service->getService('Order_Service_DiscountCode');
        $modelCart = $orderService->getCart();
        $items = $modelCart->getItems();
        
   //     Zend_Debug::dump($items);
        // liczenie wartości przedmiotów w koszyku i zapisywanie do sesji
        $itemsValue = $modelCart->countItemsValue($items);
        $modelCart->updatePrice('total_price',$itemsValue);
        $deliveryType = $deliveryTypeService->getDeliveryType((int)$modelCart->getPrice('delivery_type_id'));
        $paymentType = $paymentTypeService->getPaymentType((int)$modelCart->getPrice('payment_type_id'));
        $dataValues = $modelCart->getValues();
        $form = new Order_Form_DeliveryPayment();
      if($modelCart->getPrice('discount')!=null)
        {
            $form->getElement('code')->setAttrib('disabled','disabled');
            $form->getElement('submit')->setAttrib('disabled','disabled');
        } 
         if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    if(trim($values['code'])!="Wprowdź kod rabatowy")
                    {
                        $discountValue = $discountCodeService->getDiscountCode($values['code'])->getDiscount();
                        $discountValue *= 100;
                        $modelCart->updatePrice('discount',$discountValue);
                        $itemsValueAfterDiscount = $modelCart->countDiscountedPrice($itemsValue,$discountValue);
                        $modelCart->updatePrice('total_price_after_discount',$itemsValueAfterDiscount);
                    }
                    
                    else
                    {
                       
                        echo "Podany kod jest nieaktywny";                    
                    }
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
         }}
         // jeżeli nie jest wprowadzony kod rabatowy to cena po rabacie jest ceną produktów
         else{
              $modelCart->updatePrice('total_price_after_discount',$modelCart->getPrice('total_price'));
         }
            $prices = $modelCart->getPrices(); 
        $this->view->assign('prices',$prices);
        $this->view->assign('dataValues',$dataValues);
        $this->view->assign('deliveryType',$deliveryType);
        $this->view->assign('paymentType',$paymentType);
        $this->view->assign('form',$form);
        $this->view->assign('items',$items);
        $this->_helper->actionStack('layout', 'index', 'default');
    }
    public function thirdStepAction()
    {
        $this->_helper->layout->setLayout('login');
        $userService = $this->_service->getService('User_Service_User');
        $orderService = $this->_service->getService('Order_Service_Order');
        $deliveryAddressService = $this->_service->getService('Order_Service_DeliveryAddress');
        $deliveryService = $this->_service->getService('Order_Service_Delivery');
        $paymentService = $this->_service->getService('Order_Service_Payment');
        $itemService = $this->_service->getService('Order_Service_Item');
        
        $modelCart = $orderService->getCart();
        $dataValues = $modelCart->getValues();
        
        $deliveryAddress = $deliveryAddressService->checkDeliveryAddress($dataValues);
// zapisywanie uzytkownika
        $newDataValues = array_merge($dataValues,$deliveryAddress);
        $us = $userService->saveClientFromArray($newDataValues);
        // zapisywanie sposobu dostawy
        $das = $deliveryAddressService->saveDeliveryAddressFromArray($deliveryAddress);
        
        $deliveryData = array('delivery_type_id' => $modelCart->getPrice('delivery_type_id'),'delivery_address_id' => $das->getId());
        //laczenie adresu i sposobu dostawy
        $ds = $deliveryService->saveDeliveryFromArray($deliveryData);
        
        $ps = $paymentService->savePaymentFromArray(array('payment_type_id'=>$modelCart->getPrice('payment_type_id')));
        
        $orderData = array(
            'total_cost'=>$modelCart->countItemsValue(),
            'user_id'=>$us->getId(),
            'order_status_id'=>8,
            'delivery_id'=>$ds->getId(),
            'payment_id'=>$ps->getId());
       
        // zapisywanie zamowienia
        $os = $orderService->saveOrderFromArray($orderData);
        
        // zapisywanie przedmiotów
        $itemService->saveItemsFromArray($modelCart->getItems(),$os->getId());
       // $this->_service->get('doctrine')->getCurrentConnection()->commit();
        $modelCart->clean();
     $this->_helper->actionStack('layout', 'index', 'default');
    }
     public function shippingAction() {
        $deliveryTypeService = $this->_service->getService('Order_Service_DeliveryType');
        $paymentTypeService = $this->_service->getService('Order_Service_PaymentType');
        $deliveryAddressService = $this->_service->getService('Order_Service_DeliveryAddress');
        $paymentService = $this->_service->getService('Order_Service_Payment');
        $orderService = $this->_service->getService('Order_Service_Order');
        $deliveryService = $this->_service->getService('Order_Service_Delivery');
        $itemService = $this->_service->getService('Order_Service_Item');
      //  $produktService = $this->_service->getService('Product_Service_Product');
        $modelCart = $orderService->getCart();
      //  var_dump($modelCart->getPrices());
        $form = new User_Form_Order();
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {

                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    // laczymy podane nazwisko i imie przed wrzuceniem do bazy
                    $values['name'] = $values['last_name']." ".$values['first_name'];
                    // ustawianie statusu zamówienia jako nowe
                    $values['order_status_id'] = 8;
                    
                    // zapisywanie adresu dostawy
                    $das = $deliveryAddressService->saveDeliveryAddressFromArray($values);
                    // zapisywanie sposobu platnosci
                    $ps = $paymentService->savePaymentFromArray($values);
                    $values['payment_id'] = $ps->getId();
                    $values['delivery_address_id'] = $das->getId();
                    
                    //laczenie adresu i sposobu dostawy
                    $ds = $deliveryService->saveDeliveryFromArray($values);
                    $values['delivery_id'] = $ds->getId();
                    
                    $deliveryType = $deliveryTypeService->getDeliveryType($values['delivery_type_id']);
                    // pobranie nazwy płatności, nazwie dostawcy i koszcie dostawy
                    $values['payment_name'] = $paymentTypeService->getPaymentType($values['payment_type_id'])->getName();
                    $values['delivery_name'] = $deliveryType->getName();
                    $values['delivery_price'] = $deliveryType->getPrice();
                    
                    
                    
                    // suma kosztu wszystkich przedmiotów
                    $values['total_price'] = number_format($modelCart->getPrice('total_price_after_discount'),2);
                  // do zapłaty (koszt przedmiotów + koszt dostawy)
                    $values['total_cost'] = number_format($values['total_price'] + $values['delivery_price'],2);
                    
                    // zapisywanie zamowienia
                    $os = $orderService->saveOrderFromArray($values);
                    // zapisywanie przedmiotów

                    $itemService->saveItemsFromArray($modelCart->getItems(),$os->getId());
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    // Tworzenie sesji aby przekazać dane do podsumowania zamówienia
                    $summarySession = new Zend_Session_Namespace('summary');
                    $summarySession->summary = $values;
                    $this->_helper->redirector->gotoUrl('/order/index/summary');
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
            else{
            $form->populate($_POST);
            }
           
        }
        $this->view->assign('form',$form);
    }
    public function summaryAction()
    {
      
        $orderService = $this->_service->getService('Order_Service_Order');
        
        $modelCart = $orderService->getCart();
        $itemsCart = $modelCart->getItems();
        $summarySession = new Zend_Session_Namespace('summary');
        $values = $summarySession->summary;
        $prices = $modelCart->getPrices();
   //Zend_Debug::dump($values);
        $this->view->assign('values',$values);
        $this->view->assign('cart',$itemsCart);
        $this->view->assign('prices',$prices);
                
        $summarySession->unsetAll();
        $modelCart->clean();
        
    }
    public function clearBasketAction()
    {
        $orderService = $this->_service->getService('Order_Service_Order');
        $modelCart = $orderService->getCart();
        $modelCart->clean();
        $this->_helper->redirector->gotoUrl('/');
        

    }
    

}

