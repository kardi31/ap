<?php

/**
 * Affilateprogram_IndexController
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Affilateprogram_IndexController extends MF_Controller_Action {
    
    public function indexAction() {
        $pageService = $this->_service->getService('Page_Service_PageShop');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $affilateProgramService = $this->_service->getService('Affilateprogram_Service_Affilateprogram');
        
        if(!$page = $pageService->getI18nPage('partner-program', 'type', $this->view->language, Doctrine_Core::HYDRATE_RECORD)) {
            throw new Zend_Controller_Action_Exception('Page not found');
        }
        
        $metatagService->setViewMetatags($page->get('Metatag'), $this->view);
        
        $referenceNumber = $this->getRequest()->getParam('reference_number');
        
        if ($referenceNumber):
            $partner = $affilateProgramService->getPartner($referenceNumber, 'reference_number');
        elseif(isset($_COOKIE["reference_number"])):
            $partner = $affilateProgramService->getPartner($_COOKIE["reference_number"], 'reference_number');
        endif; 
        
        if($partner):
            $expire=time()+60*60*24*90;
            setcookie("reference_number", $partner->getReferenceNumber(), $expire, '/');
        endif; 
        
        $form = new Default_Form_Contact();
        
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
        
        $session = new Zend_Session_Namespace('CONTACT_CSRF');
        $form->getElement('csrf')->setSession($session)->initCsrfValidator();
        
        $contactEmail = "sklep@a-ti.pl";
            
        $mail = new Zend_Mail('UTF-8');
        $mail->addTo($contactEmail);
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $mail->setReplyTo($form->getValue('email'));
                    $mail->setSubject($form->getValue('subject'));
                    $mail->setBodyText($form->getValue('message'));
                    $mail->send();
                    $form->reset();
                    $this->view->messages()->add($this->view->translate('Message sent!'));
                    
                    $this->_helper->redirector->gotoRoute(array(), 'domain-i18n:affilate-program');
                } catch(Exception $e) {
                    $this->_service->get('Logger')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('form', $form);
        $this->view->assign('page', $page);
        $this->view->assign('partner', $partner);
        
        $this->_helper->actionStack('layout-shop', 'index', 'default');
    }
}

