<?php

/**
 * Faq_IndexController
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Faq_IndexController extends MF_Controller_Action {
    
    public function indexAction() {
        $faqService = $this->_service->getService('Faq_Service_Faq');
        $categoryService = $this->_service->getService('Faq_Service_Category');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        
        
        $categoryList = $categoryService->getAllCategories();
        if(strlen($this->getRequest()->getParam('category'))){
            if(!$category = $categoryService->getFullCategory($this->getRequest()->getParam('category'),'slug')) {
                $category = $categoryList[0];
            }
        }
        else{
            $category = $categoryList[0];
        }
      
        $this->view->assign('category', $category);  
        $this->view->assign('categoryList', $categoryList);  
        
        $this->_helper->actionStack('layout', 'index', 'default');
    }
    
    public function newsItemAction() {
        $newsService = $this->_service->getService('Faq_Service_Faq');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        if(!$news = $newsService->getFullFaq($this->getRequest()->getParam('slug'), 'slug')) {
            throw new Zend_Controller_Action_Exception('Faq not found', 404);
        }
        
        $metatagService->setViewMetatags($news->get('Metatags'), $this->view);
        
        $this->view->assign('news', $news);
        
        $this->_helper->actionStack('layout-ajurweda', 'index', 'default');
    }
}

