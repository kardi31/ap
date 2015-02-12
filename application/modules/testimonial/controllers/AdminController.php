<?php

/**
 * Testimonial_AdminController
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Testimonial_AdminController extends MF_Controller_Action {
   
    public function init() {
        $this->_helper->ajaxContext()
                ->addActionContext('move-testimonial', 'json')
                ->initContext();
        parent::init();
    }
    
    public function listTestimonialAction() {
        $testimonialService = $this->_service->getService('Testimonial_Service_Testimonial');
        
        if(!$testimonialRoot = $testimonialService->getTestimonialRoot()) {
            $testimonialService->createTestimonialRoot();
        }

   }
    
   public function listTestimonialDataAction() {    
        $i18nService = $this->_service->getService('Default_Service_I18n');
       
        $table = Doctrine_Core::getTable('Testimonial_Model_Doctrine_Testimonial');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Testimonial_DataTables_Testimonial', 
            'columns' => array('pt.name', 'p.created_at'),
            'searchFields' => array('pt.name')
        ));
        
        $language = $i18nService->getAdminLanguage();
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();

            $row[] = $result->author_name;
            $row[] = $result['created_at'];
            if ($result['status'] == 1)
                $row[] = '<a href="' . $this->view->adminUrl('refresh-status-testimonial', 'testimonial', array('testimonial-id' => $result->id)) . '" title=""><span class="icon16  icomoon-icon-lamp-2"></span></a>';
            else 
                $row[] = '<a href="' . $this->view->adminUrl('refresh-status-testimonial', 'testimonial', array('testimonial-id' => $result->id)) . '" title=""><span class="icon16 icomoon-icon-lamp-3"></span></a>';
            $moving = '<a href="' . $this->view->adminUrl('move-testimonial', 'testimonial', array('id' => $result->id, 'move' => 'up')) . '" class="move" title ="' . $this->view->translate('Move up') . '"><span class="icomoon-icon-arrow-up"></span></a>';     
            $moving .= '<a href="' . $this->view->adminUrl('move-testimonial', 'testimonial', array('id' => $result->id, 'move' => 'down')) . '" class="move" title ="' . $this->view->translate('Move down') . '"><span class="icomoon-icon-arrow-down"></span></a>';
            $row[] = $moving;
            $options = '<a href="' . $this->view->adminUrl('edit-testimonial', 'testimonial', array('id' => $result->id)) . '" title="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;&nbsp;';
            $options .= '<a href="' . $this->view->adminUrl('remove-testimonial', 'testimonial', array('id' => $result->id)) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
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
    
    public function addTestimonialAction() {
        $testimonialService = $this->_service->getService('Testimonial_Service_Testimonial');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $translator = $this->_service->get('translate');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $testimonialService->getTestimonialForm();
        
        if(!$parent = $testimonialService->getTestimonial($this->getRequest()->getParam('id', 0))) {
            $parent = $testimonialService->getTestimonialRoot();
        }

        $form->getElement('parent_id')->setValue($parent->getId());

        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    
                    $testimonial = $testimonialService->saveTestimonialFromArray($values);
                    
                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-testimonial', 'testimonial'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }

        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('languages', $languages);
        $this->view->assign('form', $form);
    }
    
    public function editTestimonialAction() {
        $testimonialService = $this->_service->getService('Testimonial_Service_Testimonial');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $translator = $this->_service->get('translate');
        
        if(!$testimonial = $testimonialService->getTestimonial($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Testimonial not found');
        }
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $testimonialService->getTestimonialForm($testimonial);
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    
                    $testimonial = $testimonialService->saveTestimonialFromArray($values);
                    
                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();

                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-testimonial', 'testimonial'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('languages', $languages);
        $this->view->assign('testimonial', $testimonial);
        $this->view->assign('form', $form);
    }
    
    public function removeTestimonialAction() {
        $testimonialService = $this->_service->getService('Testimonial_Service_Testimonial');
        
        if($testimonial = $testimonialService->getTestimonial($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                
                $testimonialService->removeTestimonial($testimonial);

                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-testimonial', 'testimonial'));
            } catch(Exception $e) {
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-testimonial', 'testimonial'));
    }

    public function refreshStatusTestimonialAction() {
        $testimonialService = $this->_service->getService('Testimonial_Service_Testimonial');
        
        if(!$testimonial = $testimonialService->getTestimonial((int) $this->getRequest()->getParam('testimonial-id'))) {
            throw new Zend_Controller_Action_Exception('Book not found');
        }
        
        $testimonialService->refreshStatusTestimonial($testimonial);
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-testimonial', 'testimonial'));
        $this->_helper->viewRenderer->setNoRender();
    }
    
    public function moveTestimonialAction() {
        $testimonialService = $this->_service->getService('Testimonial_Service_Testimonial');
     
        $this->view->clearVars();
        
        $testimonial = $testimonialService->getTestimonial((int) $this->getRequest()->getParam('id'));
        $status = 'success';

        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

            $testimonialService->moveTestimonial($testimonial, $this->getRequest()->getParam('move', 'down'));

            $this->_service->get('doctrine')->getCurrentConnection()->commit();
        } catch(Exception $e) {
            $this->_service->get('doctrine')->getCurrentConnection()->rollback();
            $this->_service->get('log')->log($e->getMessage());
            $status= 'error';
        }
        
        $this->_helper->viewRenderer->setNoRender();
        
        $this->view->assign('status', $status);
    }
}

