<?php

class Default_IndexController extends MF_Controller_Action
{
    
    public function indexAction()
    {
      
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $pageService = $this->_service->getService('Page_Service_Page');
        $newsService = $this->_service->getService('News_Service_News');
        $leagueService = $this->_service->getService('League_Service_League');
        $matchService = $this->_service->getService('League_Service_Match');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $photoDimensionService = $this->_service->getService('Default_Service_PhotoDimension');

        $query = $newsService->getNewsPaginationQuery($this->view->language);
        
        $adapter = new MF_Paginator_Adapter_Doctrine($query, Doctrine_Core::HYDRATE_RECORD);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setItemCountPerPage(10);
        $page = $this->_request->getParam('strona', 1);
        $paginator->setCurrentPageNumber($page);
        
        $leagues = $leagueService->getActiveLeaguesWithTable();
	$leagueIds = $leagueService->getActiveLeagueIds();
	$nextMatches = $matchService->getNextMatches($leagueIds);
	$prevMatches = $matchService->getPrevMatches($leagueIds);
	
	
	
        $eventService = $this->_service->getService('District_Service_Event');
        $nextEvents = $eventService->getNextEvents();
        $this->view->assign('nextEvents',$nextEvents);
        
        if(!$homepage = $pageService->getI18nPage('homepage', 'type', $this->view->language, Doctrine_Core::HYDRATE_RECORD)) {
            throw new Zend_Controller_Action_Exception('Homepage not found');
        }
        
        if($homepage != NULL):
            $metatagService->setViewMetatags($homepage->get('Metatag'), $this->view);
        endif;
        
        $this->view->assign('homepage', $homepage);
        
        
        $this->view->assign('prevMatches', $prevMatches);
        $this->view->assign('nextMatches', $nextMatches);
        $this->view->assign('leagues', $leagues);
        $this->view->paginator = $paginator;
        $this->view->modelPhoto = $photoService;
        $this->_helper->actionStack('layout');
        
    }

    public function layoutAction()
    {
        $teamService = $this->_service->getService('League_Service_Team');
        $this->view->assign('teamService',$teamService);
        
        $galleryService = $this->_service->getService('Gallery_Service_Gallery');
        $galleryList = $galleryService->getAllGallerys();
        $this->view->assign('galleryList',$galleryList);
        
        $cupService = $this->_service->getService('League_Service_Cup');
        $cupsList = $cupService->getAllCups();
        $this->view->assign('cupsList',$cupsList);
        
//        $bannerService = $this->_service->getService('Banner_Service_Banner');
//        $bannerLeft = $bannerService->getCategoryBanners(1);
//        $bannerRight = $bannerService->getCategoryBanners(2);
//        
//        $this->view->assign('bannerLeft',$bannerLeft);
//        $this->view->assign('bannerRight',$bannerRight);
        
        $photoDimensionService = $this->_service->getService('Default_Service_PhotoDimension');

        $sponsorPhotoDimension = $photoDimensionService->getElementDimension('sponsor');
        
        $this->view->assign('sponsorPhotoDimension', $sponsorPhotoDimension);
        $this->_helper->actionStack('sidebar');
        $this->_helper->actionStack('slider');
        $this->_helper->actionStack('menu');
        $this->_helper->viewRenderer->setNoRender(true);
    }


    public function leftSidebarAction()
    {
       
        $modelNews = new Application_Model_News();
        
        
        
        $news = $modelNews->getAllNewsNoPagination(6);
       
        $this->view->assign('news',$news);
        
        $this->_helper->viewRenderer->setResponseSegment('leftSidebar');
    }

    public function sidebarAction()
    {
        $resultService = $this->_service->getService('League_Service_Match');
          
        $results = $resultService->getLastResults();
        $this->view->assign('lastResults',$results);
        $this->_helper->viewRenderer->setResponseSegment('sidebar');
    }

    public function footerAction()
    {
        $this->_helper->viewRenderer->setResponseSegment('footer');
    }

    public function showNewsAction()
    {
         $modelNews = new Application_Model_News();
         $modelPhoto = new Application_Model_Photo();
        
        
//         $allNews = $modelNews->getAllNewsNoPagination(1500);
//    
//         foreach($allNews as $n):
//        //     echo "ok";
//             $slug = Application_Model_News::createUniqueTableSlug('aktualnosci', $n['tytul']);
//             $modelNews->addSlug($n['id_news'],$slug);
//         endforeach;
//         exit;
        if(!$news = $modelNews->getNews($this->getRequest()->getParam('slug'))){
            throw new Zend_Exception('News not found');
        }
        $this->view->assign('news',$news);
        $this->view->modelPhoto = $modelPhoto;
        $this->_helper->actionStack('layout');
    }

    public function showResultAction()
    {
       echo $this->getRequest()->getParam('slug');
    }

    public function sponsorsAction()
    {
        $this->_helper->actionStack('layout');
    }

    public function zarzadAction()
    {
        $this->_helper->actionStack('layout');
    }

    public function archiwumAction()
    {
        // action body
    }
    
    public function sliderAction() {
        $sliderService = $this->_service->getService('Slider_Service_Slider');
        $slideLayerService = $this->_service->getService('Slider_Service_SlideLayer');
        $mainSliderSlides = $sliderService->getAllForSlider("main");
        $mainSlides = array();
        foreach($mainSliderSlides[0]['Slides'] as $slide):
            $layers = $slideLayerService->getLayersForSlide($slide['id']);
            $slide['Layers'] = $layers;
            $mainSlides[] = $slide;
        endforeach;
        $this->view->assign('mainSlides',$mainSlides);
        $this->_helper->viewRenderer->setResponseSegment('slider');
	
	
        $videoService = $this->_service->getService('Gallery_Service_Video');
	$promotedVideo = $videoService->getPromotedVideo();
	
        $this->view->assign('promotedVideo', $promotedVideo);
    }
    
    public function menuAction(){
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $menuService = $this->_service->getService('Menu_Service_Menu');
        
       
        if(!$menu = $menuService->getMenu(1)) {
            throw new Zend_Controller_Action_Exception('Menu not found');
        }
        
        $treeRoot = $menuService->getMenuItemTree($menu, $this->view->language);
        $tree = $treeRoot[0]->getNode()->getChildren();
            
        $activeLanguages = $i18nService->getLanguageList();
        
        $this->view->assign('activeLanguages', $activeLanguages);
        
        $this->view->assign('menu', $menu);
        $this->view->assign('tree', $tree);
        
        $this->_helper->viewRenderer->setNoRender();
    }
}
