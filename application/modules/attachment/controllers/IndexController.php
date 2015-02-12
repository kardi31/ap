<?php

/**
 * Attachment_IndexController
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Attachment_IndexController extends MF_Controller_Action {
 
   public function attachmentsSerwis5Action(){
       $attachmentService = $this->_service->getService('Attachment_Service_AttachmentSerwis5');
       
       $attachments = $attachmentService->getAllAttachments($this->view->language, Doctrine_Core::HYDRATE_ARRAY);
       
       $hideSlider = true;
       $this->view->assign('hideSlider', $hideSlider);
       $this->view->assign('attachments', $attachments);

       $this->_helper->actionStack('layout-serwis5', 'index', 'default');
   }
   
   public function showPdfAttachmentSerwis5Action() {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        
        $attachmentService = $this->_service->getService('Attachment_Service_AttachmentSerwis5');
        
        if(!$attachment = $attachmentService->getFullAttachment($this->getRequest()->getParam('attachment'), 'slug')) {
            throw new Zend_Controller_Action_Exception('Attachment not found', 404);
        }

        header ('Content-Type:', 'application/pdf');
        header ('Content-Disposition:', 'inline;');
        
        $fileName = "media/attachments/".$attachment->getFileName(); 
        $pdf = new Zend_Pdf($fileName, null, true); 

        echo $pdf->render();
    }
}

