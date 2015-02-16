<?php

class Default_IndexController extends MF_Controller_Action
{
    
    public function indexAction()
    {
      
        $this->_helper->actionStack('layout');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $pageService = $this->_service->getService('Page_Service_Page');
        $newsService = $this->_service->getService('News_Service_News');
        $photoService = $this->_service->getService('Media_Service_Photo');

        $query = $newsService->getNewsPaginationQuery($this->view->language);
        
        $adapter = new MF_Paginator_Adapter_Doctrine($query, Doctrine_Core::HYDRATE_RECORD);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setItemCountPerPage(10);
        $page = $this->_request->getParam('strona', 1);
        $paginator->setCurrentPageNumber($page);
        
        $leagueService = $this->_service->getService('League_Service_League');
        $matchService = $this->_service->getService('League_Service_Match');
        $leagues = $leagueService->getActiveLeaguesWithTable();
	$leagueIds = $leagueService->getActiveLeagueIds();
	$nextMatches = $matchService->getNextMatches($leagueIds);
	$prevMatches = $matchService->getPrevMatches($leagueIds);
	
        $this->view->assign('prevMatches', $prevMatches);
        $this->view->assign('nextMatches', $nextMatches);
        $this->view->assign('leagues', $leagues);
	
	
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
        
        
        $this->view->paginator = $paginator;
        $this->view->modelPhoto = $photoService;
        
//        var_dump(isset($this->view->nextEvents));exit;
    }

    public function layoutAction()
    {
        
       
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

     public function contactAction() {
        
        
        $pageService = $this->_service->getService('Page_Service_Page');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $serviceService = $this->_service->getService('Default_Service_Service');
        
        
        if(!$page = $pageService->getI18nPage('contact', 'type', $this->language, Doctrine_Core::HYDRATE_RECORD)) {
            throw new Zend_Controller_Action_Exception('Page not found');
        }
 
        $contactEmail = $this->getInvokeArg('bootstrap')->getOption('contact_email');
        $mailerEmail = $this->getInvokeArg('bootstrap')->getOption('mailer_email');
        
        if ($page != NULL):
            $metatagService->setViewMetatags($page->get('Metatag'), $this->view);
        endif;
        $form = new Default_Form_Contact();
        
	$form->getElement('name')->clearDecorators();
	$form->getElement('name')->addDecorator('viewHelper');
	
	$form->getElement('email')->clearDecorators();
	$form->getElement('email')->addDecorator('viewHelper');
	
	$form->getElement('subject')->clearDecorators();
	$form->getElement('subject')->addDecorator('viewHelper');
	
	$form->getElement('message')->clearDecorators();
	$form->getElement('message')->addDecorator('viewHelper');
	
        $captchaDir = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('captchaDir');
        $form->addElement('captcha', 'captcha',
            array(
            'label' => 'Rewrite the chars', 
            'captcha' => array(
                'captcha' => 'Image',  
                'wordLen' => 5,  
                'timeout' => 300,  
                'font' => APPLICATION_PATH . '/../data/arial.ttf',  
                'imgDir' => $captchaDir,  
                'imgUrl' => $this->view->serverUrl() . '/captcha/',  
            )
        ));
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    
                   if(!strlen($contactEmail)){
                        $this->_helper->redirector->gotoUrl($this->view->url(array('success' => 'fail'), 'domain-contact'));
                    }
                    $values = $_POST;
                    $serviceService->sendMail($values,$contactEmail,$mailerEmail);
                    
                    $this->view->messages()->add($this->view->translate('Message sent'));
                    $this->_helper->redirector->gotoUrl($this->view->url(array('success' => 'fail'), 'domain-contact'));
                    
                } catch(Exception $e) {
                    var_dump($e->getMessage());exit;
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
          

        $this->view->assign('form', $form);
        $this->view->assign('page', $page);
        $this->view->assign('hideSlider', true);
        $this->view->assign('success',$this->getRequest()->getParam('success'));
        $this->_helper->actionStack('layout', 'index', 'default');
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
