<?php

/**
 * Order_AdminController
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Tournament_AdminController extends MF_Controller_Action {
    
    public function listGroupAction() {
        $groupService = $this->_service->getService('Tournament_Service_Team');
       
        $tournament_id = $this->getRequest()->getParam('tournament_id');
        $groups = $groupService->getGroupTeams();
        
        $this->view->assign('groups', $groups);
        $this->view->assign('tournament_id', $tournament_id);
    }
    
    public function addTeamAction() {
        $teamService = $this->_service->getService('Tournament_Service_Team');
        
//        if(!$booking = $bookingService->getSingleBooking((int) $this->getRequest()->getParam('id'))) {
//            throw new Zend_Controller_Action_Exception('Booking not found');
//        }
        
        $tournament_id = $this->getRequest()->getParam('tournament_id');
        
        $form = $teamService->getGroupTeamForm();
        
         
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();

                    $values['tournament_id'] = $tournament_id;
                    $team = $teamService->saveTeamFromArray($values);

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-group', 'tournament',array('tournament_id' => $tournament_id)));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('tournament_id', $tournament_id);
        $this->view->assign('form', $form);
    }
    
    public function editGroupAction() {
        $bookingService = $this->_service->getService('Tournament_Service_Booking');
        $playerService = $this->_service->getService('Tournament_Service_Player');
        
        if(!$booking = $bookingService->getSingleBooking((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Booking not found');
        }
        
        $tournament_id = $this->getRequest()->getParam('tournament_id');
        
        $form = $bookingService->getBookingForm($booking);
        $form->getElement('player_id')->setMultiOptions($playerService->prependPlayerOptions($tournament_id));
        $form->getElement('player_id')->setValue($booking['player_id']);
         
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();

                    $booking = $bookingService->saveBookingFromArray($values);

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-booking', 'tournament',array('tournament_id' => $tournament_id)));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('tournament_id', $tournament_id);
        $this->view->assign('form', $form);
    }
    
    public function setGroupActiveAction() {
        $bookingService = $this->_service->getService('Tournament_Service_Booking');
        
        if(!$booking = $bookingService->getSingleBooking((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Booking not found');
        }
        if($booking->get('active'))
            $booking->set('active',false);
        else
            $booking->set('active',true);
        $booking->save();
        $this->_helper->redirector->gotoUrl($_SERVER['HTTP_REFERER']);
               
    } 
    
    public function removeGroupAction() {
        $bookingService = $this->_service->getService('Tournament_Service_Booking');
        
        if(!$booking = $bookingService->getSingleBooking((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Booking not found');
        }
        
        $booking->delete();
        $this->_helper->redirector->gotoUrl($_SERVER['HTTP_REFERER']);
               
    } 
    
    
    public function listTournamentAction() {
        
    }
    
    public function listTournamentDataAction() {
        $table = Doctrine_Core::getTable('Tournament_Model_Doctrine_Tournament');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Tournament_DataTables_Tournament', 
            'columns' => array('l.id','l.name'),
            'searchFields' => array('l.id','l.name')
        ));
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();
            
            $row[] = $result['id'];
            $row[] = $result['name'];
           
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
    
    /* tournament - finish */
    
    /* match - start */
    
    public function listMatchAction() {
        $tournament_id = $this->getRequest()->getParam('tournament_id');
        $this->view->assign('tournament_id',$tournament_id);
    }
    
    public function listMatchDataAction() {
        $table = Doctrine_Core::getTable('Tournament_Model_Doctrine_Match');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Tournament_DataTables_Match', 
            'columns' => array('m.id','t1.name','t2.name','m.match_date'),
            'searchFields' => array('m.id','t1.name','t2.name','m.match_date')
        ));
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();
            
            $row[] = $result['id'];
            $row[] = $result['Team1']['name'];
            $row[] = $result['Team2']['name'];
            $goal = "Edit &nbsp; &nbsp; <input type = 'checkbox' rel = '".$result['id']."' /><br /><br />";
            $goal .= "<input type = 'text' size='4' style='width:20px' disabled parent = '".$result['id']."' inp_id = '".$result['id']."_1' value='".$result['goal1']."' />&nbsp;";
            $goal .= "<input type = 'text' size = '4' style='width:20px' disabled parent = '".$result['id']."' inp_id = '".$result['id']."_2' value='".$result['goal2']."' /><br />";
            $goal .= "<input type = 'submit' sub_id = '".$result['id']."' disabled parent = '".$result['id']."' name='submit' value='submit' />";
            
            $row[] = $goal;
            $row[] = $result['match_date'];
            $options = '<a href="' . $this->view->adminUrl('edit-match', 'tournament', array('id' => $result['id'])) . '" title ="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;&nbsp;';     
            
            $options .= '<a href="' . $this->view->adminUrl('remove-match', 'tournament', array('id' => $result['id'])) . '" title ="' . $this->view->translate('Edit') . '"><span class="icon16 icon-remove"></span></a>&nbsp;&nbsp;';     
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
    
    public function saveMatchAction() {
        $matchService = $this->_service->getService('Tournament_Service_Match');
        
        if(!$match = $matchService->getMatch((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Match not found');
        }
        
        $match->setGoal1($this->getRequest()->getParam('goal1'));
        $match->setGoal2($this->getRequest()->getParam('goal2'));
        $match->setPlayed();
        $match->save();
        $response = array(
            "success" => "success"
        );
        $this->_helper->json($response);
    }
    
    public function editMatchAction() {
        $matchService = $this->_service->getService('Tournament_Service_Match');
        $teamService = $this->_service->getService('Tournament_Service_Team');
        $shooterService = $this->_service->getService('Tournament_Service_Shooter');
        if(!$match = $matchService->getMatch((int) $this->getRequest()->getParam('id'),'id')) {
            throw new Zend_Controller_Action_Exception('Match not found');
        }
        $players1 = $teamService->getTeamPlayers($match['team1'],'id');
        $players2 = $teamService->getTeamPlayers($match['team2'],'id');
        $shooters = $shooterService->getMatchShooters($match['id']);
//        Zend_Debug::dump($shooters);exit;
        
    if(isset($_POST['submit'])){
        $values = $_POST;
        $matchService->saveShootersFromArray($values,$match);
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-match', 'tournament',array('tournament_id' => $match->tournament_id)));
                
    }
        
        $this->view->assign('match',$match);
        $this->view->assign('players1',$players1);
        $this->view->assign('players2',$players2);
        $this->view->assign('shooters',$shooters);
        
    }
    
    /* match - finish */
    
    public function timetableAction() {
        $matchService = $this->_service->getService('Tournament_Service_Match');
        $teamService = $this->_service->getService('Tournament_Service_Team');
        $tournamentService = $this->_service->getService('Tournament_Service_Tournament');
        $form = new Tournament_Form_Timetable();
        
        if(!$tournament = $tournamentService->getTournament((int) $this->getRequest()->getParam('tournament_id'))) {
            throw new Zend_Controller_Action_Exception('Tournament not found');
        }
        $teams = $teamService->getTournamentTeams($tournament->id,'id');
        
        $teamsTimetable = $teamService->getTeamsTimetable($tournament->id);
        for($i=1;$i<41;$i++):
            $t = $form->getElement('team'.$i);
            foreach($teams as $team):
                $t->addMultiOption($team->id,$team->name);
            endforeach;
        endfor;
        
        
        $this->view->assign('teamsTimetable',$teamsTimetable);
        $this->view->assign('tournament',$tournament);
        $this->view->assign('form',$form);
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {                                   
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                   
                    $values = $form->getValues();  
                    $values['tournament_id'] = $tournament->id;
                    
                    $matchService->saveTimetableFromArray($values); 

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('timetable', 'tournament',array('tournament_id' => $tournament->id)));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }    
        
    }
    
    public function listBookingAction() {
        $tournament_id = $this->getRequest()->getParam('tournament_id');
        
        $this->view->assign('tournament_id', $tournament_id);
    }
    
    public function listBookingDataAction() {
        $table = Doctrine_Core::getTable('Tournament_Model_Doctrine_Booking');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Tournament_DataTables_Booking', 
            'columns' => array('b.id','p.last_name', 't.name','p.weight'),
            'searchFields' => array('b.id','p.last_name', 't.name')
        ));
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row[] = $result['id'];
            $row[] = $result['Player']['last_name']." ".$result['Player']['first_name'];
            $row[] = $result['Player']['Team']['name'];
            if($result['weight']==2)
                $row[] = "Czerwona";
            else
                $row[] = "Żółta";
            
            $row[] = $result['quantity'];
            if($result['active']==1)
                $row[] = '<a href="' . $this->view->adminUrl('set-booking-active', 'tournament', array('id' => $result['id'])) . '" title="' . $this->view->translate('Aktywna') . '"><span class="icon16 icomoon-icon-checkbox"></span></a>';
            else
                $row[] = '<a href="' . $this->view->adminUrl('set-booking-active', 'tournament', array('id' => $result['id'])) . '" title="' . $this->view->translate('Nieaktywna') . '"><span class="icon16 icomoon-icon-checkbox-unchecked"></span></a>';
            
            $options = '<a href="' . $this->view->adminUrl('edit-booking', 'tournament', array('id' => $result['id'])) .'/tournament_id/'.$result['Player']['Team']['tournament_id']. '" title ="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;';     
             
            $options .= '<a href="' . $this->view->adminUrl('remove-booking', 'tournament', array('id' => $result['id'])) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icomoon-icon-remove"></span></a>';
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
    
    public function addBookingAction() {
        $bookingService = $this->_service->getService('Tournament_Service_Booking');
        $playerService = $this->_service->getService('Tournament_Service_Player');
        
//        if(!$booking = $bookingService->getSingleBooking((int) $this->getRequest()->getParam('id'))) {
//            throw new Zend_Controller_Action_Exception('Booking not found');
//        }
        
        $tournament_id = $this->getRequest()->getParam('tournament_id');
        
        $form = $bookingService->getBookingForm();
        $form->getElement('player_id')->setMultiOptions($playerService->prependPlayerOptions($tournament_id));
        
         
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();

                    $booking = $bookingService->saveBookingFromArray($values);

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-booking', 'tournament',array('tournament_id' => $tournament_id)));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('tournament_id', $tournament_id);
        $this->view->assign('form', $form);
    }
    
    public function editBookingAction() {
        $bookingService = $this->_service->getService('Tournament_Service_Booking');
        $playerService = $this->_service->getService('Tournament_Service_Player');
        
        if(!$booking = $bookingService->getSingleBooking((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Booking not found');
        }
        
        $tournament_id = $this->getRequest()->getParam('tournament_id');
        
        $form = $bookingService->getBookingForm($booking);
        $form->getElement('player_id')->setMultiOptions($playerService->prependPlayerOptions($tournament_id));
        $form->getElement('player_id')->setValue($booking['player_id']);
         
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();

                    $booking = $bookingService->saveBookingFromArray($values);

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-booking', 'tournament',array('tournament_id' => $tournament_id)));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('tournament_id', $tournament_id);
        $this->view->assign('form', $form);
    }
    
    public function setBookingActiveAction() {
        $bookingService = $this->_service->getService('Tournament_Service_Booking');
        
        if(!$booking = $bookingService->getSingleBooking((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Booking not found');
        }
        if($booking->get('active'))
            $booking->set('active',false);
        else
            $booking->set('active',true);
        $booking->save();
        $this->_helper->redirector->gotoUrl($_SERVER['HTTP_REFERER']);
               
    } 
    
    public function removeBookingAction() {
        $bookingService = $this->_service->getService('Tournament_Service_Booking');
        
        if(!$booking = $bookingService->getSingleBooking((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Booking not found');
        }
        
        $booking->delete();
        $this->_helper->redirector->gotoUrl($_SERVER['HTTP_REFERER']);
               
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
    public function listDiscountCodeDataAction()
    {
        $table = Doctrine_Core::getTable('Order_Model_Doctrine_DiscountCode');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Order_DataTables_DiscountCode', 
            'columns' => array('dc.code','dc.active','dc.id'),
            'searchFields' => array('dc.active')
        ));
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();

            $row[] = $result['code'];
            if($result['active'])
                $row[] = "tak";
            else 
                $row[] = "nie";
            $row[] = ($result['discount']*100)."%";
            $options = '<a href="' . $this->view->adminUrl('edit-discount-code', 'order', array('id' => $result['id'])) . '" title ="' . $this->view->translate('Change status') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;';     
            $row[] = $options;
            $rows[] = $row;
        }
       // Zend_Debug::dump($rows);
        $response = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $dataTables->getDisplayTotal(),
            "iTotalDisplayRecords" => $dataTables->getTotal(),
            "aaData" => $rows
        );
        $this->_helper->json($response);
    }
    public function editDiscountCodeAction() {
        $discountCodeService = $this->_service->getService('Order_Service_DiscountCode');
        
        $translator = $this->_service->get('translate');
//        $discountCode = $discountCodeService->getDiscountCode(2);
//        echo $discountCode;
//        exit;
        if(!$discountCode = $discountCodeService->getDiscountCodeById((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Discount Code not found');
        }
                try { 
                    if($discountCode['active'])
                        $status = 0;
                    else
                        $status = 1;
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
               
                    $discountCodeService->changeActiveStatus((int) $this->getRequest()->getParam('id'),$status);

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('order-discount', 'order'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
                  $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();

//        $this->view->assign('form', $form);
//        $this->view->assign('orderStatus', $orderStatus);
    } 
       public function addDiscountCodeAction() {
        $discountCodeService = $this->_service->getService('Order_Service_DiscountCode');
        
        $translator = $this->_service->get('translate');
        
        $form = $discountCodeService->getDiscountCodeForm();
        
        $form->setAction($this->view->adminUrl('add-discount-code', 'order'));
           
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                   
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                     $values = $form->getValues();
                    

                    $discountCodeService = $discountCodeService->saveDiscountCodeFromArray($values);

                    $this->view->messages()->add($translator->translate('Discount code has been added'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('order-discount', 'order'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        $this->view->assign('form', $form);
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
            
            $options = '<a href="' . $this->view->adminUrl('edit-order-status', 'order', array('id' => $result['id'])) . '" title ="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;';     
            $options .= '<a href="' . $this->view->adminUrl('remove-order-status', 'order', array('id' => $result['id'])) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
            $row[] = $options;
            $rows[] = $row;
        }
       // Zend_Debug::dump($rows);
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
        $code = 'F-'.MF_Text::timeFormat($order['created_at'], 'Ymd').'-'.$order['id'];
        
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
}

