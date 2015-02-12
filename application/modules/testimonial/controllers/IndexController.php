<?php

/**
 * Testimonial_IndexController
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Testimonial_IndexController extends MF_Controller_Action {
    
    public function testimonialsAction() {
        $pageService = $this->_service->getService('Page_Service_Page');
        $testimonialService = $this->_service->getService('Testimonial_Service_Testimonial');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $partnerItemCountPerPage = $partnerService->getPartnerItemCountPerPage();
       
        if(!$page = $pageService->getI18nPage('partners', 'type', $this->view->language, Doctrine_Core::HYDRATE_RECORD)) {
            throw new Zend_Controller_Action_Exception('Page not found');
        }
        
        $metatagService->setViewMetatags($page->get('Metatag'), $this->view);
        
        $query = $partnerService->getPartnersPaginationQuery($this->view->language);

        $adapter = new MF_Paginator_Adapter_Doctrine($query, Doctrine_Core::HYDRATE_ARRAY);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
        $paginator->setItemCountPerPage($partnerItemCountPerPage);

        $this->view->assign('page', $page);
        $this->view->assign('paginator', $paginator);  
        
        $this->_helper->actionStack('layout-ajurweda', 'index', 'default');
    }
}

