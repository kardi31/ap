<?php

/**
 * Affilateprogram_AdminController
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Affilateprogram_AdminController extends MF_Controller_Action {
    
    public static $itemCountPerPage = 15;
    
    public function listPartnerAction() {
        
    }
    
    public function listPartnerDataAction() {
        $table = Doctrine_Core::getTable('Affilateprogram_Model_Doctrine_Partner');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Affilateprogram_DataTables_Partner', 
            'columns' => array('p.name'),
            'searchFields' => array('p.name')
        ));
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row[] = $result->name;
            $row[] = $result->reference_number;
            $options = '<a href="' . $this->view->adminUrl('check-commission', 'affilateprogram', array('id' => $result->id)) . '" title="' . $this->view->translate('Edit') . '"><span class="icon24 icomoon-icon-history"></span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            $options .= '<a href="' . $this->view->adminUrl('edit-partner', 'affilateprogram', array('id' => $result->id)) . '" title="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            $options .= '<a href="' . $this->view->adminUrl('remove-partner', 'affilateprogram', array('id' => $result->id)) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
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
    
    public function addPartnerAction() {
        $affilateProgramService = $this->_service->getService('Affilateprogram_Service_Affilateprogram');
        
        $translator = $this->_service->get('translate');
        
        $form = $affilateProgramService->getPartnerForm();
        
        $form->setAction($this->view->adminUrl('add-partner', 'affilateprogram'));
           
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();

                    $affilateProgramService->savePartnerWithNewNumberFromArray($values);

                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-partner', 'affilateprogram'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        $this->view->assign('form', $form);
    }
    
    public function editPartnerAction() {
        $affilateProgramService = $this->_service->getService('Affilateprogram_Service_Affilateprogram');
        
        $translator = $this->_service->get('translate');
        
        if(!$partner = $affilateProgramService->getPartner((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Partner not found');
        }
        
        $form = $affilateProgramService->getPartnerForm($partner);
        $form->setAction($this->view->adminUrl('edit-partner', 'affilateprogram'));
       
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
                try {                                   
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                   
                    $values = $form->getValues();  
                    
                    $affilateProgramService->savePartnerFromArray($values); 

                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-partner', 'affilateprogram'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }    
       
        $this->view->assign('form', $form);
        $this->view->assign('partner', $partner);
    } 
    
    public function removePartnerAction() {
        $affilateProgramService = $this->_service->getService('Affilateprogram_Service_Affilateprogram');

        if($partner = $affilateProgramService->getPartner($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                
                $affilateProgramService->removePartner($partner);
                     
                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-partner', 'affilateprogram'));
            } catch(Exception $e) {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                $this->_service->get('log')->log($e->getMessage(), 4);
            }
        }      
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-partner', 'affilateprogram')); 
    }
    
    public function checkCommissionAction() {
        $affilateProgramService = $this->_service->getService('Affilateprogram_Service_Affilateprogram');
        
        $translator = $this->_service->get('translate');
        
        if(!$partner = $affilateProgramService->getPartner((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Partner not found');
        }

        $form = $affilateProgramService->getCheckCommissionForm();
           
        $commission = 0;
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();

                    $partnerOrders = $affilateProgramService->getOrdersForPartner($partner->getId(), $values);
                    foreach($partnerOrders[0]['PartnerOrders'] as $order):
                        $commission = $commission + $order['Order']['total_cost']*$partner->getCommission()/100;
                    endforeach;
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('commission', $commission);
        $this->view->assign('form', $form);
        $this->view->assign('partner', $partner);
    }
    
}

