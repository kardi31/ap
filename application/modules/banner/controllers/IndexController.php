<?php

/**
 * Banner_IndexController
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Banner_IndexController extends MF_Controller_Action {
 
   public function listBannerAction(){
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        
        if(!$banners = $bannerService->getAllBanners()) {
            throw new Zend_Controller_Action_Exception('Banners not found ');
        }
        
        
        $this->view->assign('banners', $banners);
        $this->_helper->actionStack('layout-serwis10', 'index', 'default');
        
    }
    public function bannerRightAction(){
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        
        $rightBanners = $bannerService->getPositionBanners('Sidebar1');
        $this->_helper->viewRenderer->setResponseSegment('bannerRight');
        $this->view->assign('rightBanners', $rightBanners);
    }
    
    public function bannerSidebar2Action(){
        
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        
        $banners = $bannerService->getPositionBanners('Sidebar2');
        
        $this->_helper->viewRenderer->setResponseSegment('bannerSidebar2');
        
        $this->view->assign('banners', $banners);
    }
    
    public function bannerSidebar3Action(){
        
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        
        $banners = $bannerService->getPositionBanners('Sidebar3');
        
        $this->_helper->viewRenderer->setResponseSegment('bannerSidebar3');
        
        $this->view->assign('banners', $banners);
    }
    
    public function bannerMainFirstAction(){
        
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        
        $banners = $bannerService->getPositionBanners('MainFirst');
        $this->view->assign('banners', $banners);
    }
    
    public function bannerMainSecondAction(){
        
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        
        $banners = $bannerService->getPositionBanners('MainSecond');
        
        
        $this->view->assign('banners', $banners);
    }
    
    public function bannerMainThirdAction(){
        
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        
        $banners = $bannerService->getPositionBanners('MainThird');
        
        
        $this->view->assign('banners', $banners);
    }
    
    public function bannerMainFourthAction(){
        
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        
        $banners = $bannerService->getPositionBanners('MainFourth');
        
        
        $this->view->assign('banners', $banners);
    }
    
    public function bannerMainFifthAction(){
        
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        
        $banners = $bannerService->getPositionBanners('MainFifth');
        
        
        $this->view->assign('banners', $banners);
    }
    
    public function bannerUnderNewsAction(){
        
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        
        $banners = $bannerService->getPositionBanners('UnderNews');
        
        
        $this->view->assign('banners', $banners);
    }
    
    public function bannerUnderStreamAction(){
        
        $bannerService = $this->_service->getService('Banner_Service_Banner');
        
        $banners = $bannerService->getPositionBanners('UnderStream');
        
        
        $this->view->assign('banners', $banners);
    }
    
}

