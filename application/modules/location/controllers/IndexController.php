<?php

/**
 * Location_IndexController
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Location_IndexController extends MF_Controller_Action {
    
     public function indexAction() {
        $locationService = $this->_service->getService('Location_Service_Location');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $locations = $locationService->getAlli18nLocations('lt.title',$this->view->language,DOCTRINE_CORE::HYDRATE_ARRAY);
        $locationsJson = $locationService->getLocationsForJson($this->view->language,DOCTRINE_CORE::HYDRATE_SCALAR);
        $this->view->assign('locations', $locations);  
        $this->view->assign('locationsJson', $locationsJson);  
        
        
        $this->_helper->actionStack('layout', 'index', 'default');
    }
    
    public function jsonAction() {
        $locationService = $this->_service->getService('Location_Service_Location');
        
        $locationsJson = $locationService->getLocationsForJson($this->view->language,DOCTRINE_CORE::HYDRATE_SCALAR);
        $this->view->assign('locationsJson', $locationsJson);  
        
        $response = array(
            "locationsJson" => $locationsJson,
        );

        $this->_helper->json($response);
    }
    
    public function savejsonAction() {
        $locationService = $this->_service->getService('Location_Service_Location');
        
        $values = array(
            'id' => $this->getRequest()->getParam('id'),
            'lat' => $this->getRequest()->getParam('lat'),
            'lng' => $this->getRequest()->getParam('lng'),
        );
        $locationService->saveLocationFromArray($values);
        
        $this->_helper->layout->disableLayout();
    }
    
    public function locationAction() {
        $locationService = $this->_service->getService('Location_Service_Location');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $translator = $this->_service->get('translate');
        
        if(!$location = $locationService->getFullLocation($this->getRequest()->getParam('location'),'slug')){
            throw new Zend_Controller_Action_Exception('Location not found');
        }
        $metatagService->setViewMetatags($location['metatag_id'],$this->view);
        $form = new Default_Form_Contact();
        
        $form->getElement('name')->setAttrib('placeholder', $translator->translate('Name'));
        $form->getElement('email')->setAttrib('placeholder', $translator->translate('Email'));
        $form->getElement('phone')->setAttrib('placeholder', $translator->translate('Phone'));
        $form->getElement('subject')->setAttrib('placeholder', $translator->translate('Subject'));
        $form->getElement('message')->setAttrib('placeholder', $translator->translate('Your Message ...'));
        
        $captchaDir = $this->getFrontController()->getParam('bootstrap')->getOption('captchaDir');
        
        $form->addElement('captcha', 'captcha',
            array(
            'label' => 'Rewrite the chars',  
            'captcha' => array(
                'captcha' => 'Image',  
                'wordLen' => 4,  
                'timeout' => 300,  
                'font' => APPLICATION_PATH . '/../data/arial.ttf',  
                'imgDir' => $captchaDir,  
                'imgUrl' => $this->view->serverUrl() . '/captcha/',  
                'width' => 150,
                'fontSize' => 25
            )   
        )); 
        
        $form->getElement('captcha')->setAttrib('class', 'form-control');
        
        $session = new Zend_Session_Namespace('CONTACT_CSRF');
        $form->getElement('csrf')->setSession($session)->initCsrfValidator();
        
        $options = $this->getFrontController()->getParam('bootstrap')->getOptions();
        $contactEmail = $options['reply_email'];
            
        $mail = new Zend_Mail('UTF-8');
        $mail->addTo($contactEmail);
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $contentMessage = $form->getValue('name')."\n";
                    $contentMessage .= $form->getValue('phone')."\n";
                    $contentMessage .= $form->getValue('message')."\n";
                    $mail->setReplyTo($form->getValue('email'));
                    $mail->setSubject($form->getValue('subject'));
                    $mail->setBodyText($contentMessage);
                    $mail->send();
                    $form->reset();
                    $this->view->messages()->add($this->view->translate('Message sent!'));
                    
                    $this->_helper->redirector->gotoRoute(array('location' => $location['Translation'][$this->view->language]['title']), 'domain-location');
                } catch(Exception $e) {
                    $this->_service->get('Logger')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('location', $location);  
        $this->view->assign('form', $form);   
        
        $this->_helper->actionStack('layout', 'index', 'default');
    }
}

