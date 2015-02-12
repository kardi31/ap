<?php

/**
 * Faq_AdminController
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Faq_AdminController extends MF_Controller_Action {
    
    public static $itemCountPerPage = 15;
    
    public function listFaqAction() {

    }
    
    public function listFaqDataAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $table = Doctrine_Core::getTable('Faq_Model_Doctrine_Faq');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Faq_DataTables_Faq', 
            'columns' => array('xt.title', 'ct.title','x.created_at'),
            'searchFields' => array('xt.title', 'ct.title')
        ));
        
        $results = $dataTables->getResult();
        
        $language = $i18nService->getAdminLanguage();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();
            //$row['DT_RowId'] = $result->id;
            $row[] = $result->Translation[$language->getId()]->title;
           
            $row[] = $result['Category']['Translation'][$language->getId()]->title;
            $row[] = MF_Text::timeFormat($result->created_at, 'H:i d/m/Y');
            $options = '<a href="' . $this->view->adminUrl('edit-faq', 'faq', array('id' => $result->id)) . '" title="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            $options .= '<a href="' . $this->view->adminUrl('remove-faq', 'faq', array('id' => $result->id)) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
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
    
    public function addFaqAction() {
        $faqService = $this->_service->getService('Faq_Service_Faq');
        $categoryService = $this->_service->getService('Faq_Service_Category');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $translator = $this->_service->get('translate');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $faqService->getFaqForm();
        
        $metatagsForm = $metatagService->getMetatagsSubForm();
        $form->addSubForm($metatagsForm, 'metatags');
      
        $form->getElement('category_id')->setMultiOptions($categoryService->getTargetCategorySelectOptions(true,$adminLanguage->getId()));

        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    
                    if($metatags = $metatagService->saveMetatagsFromArray(null, $values, array('title' => 'title', 'description' => 'content', 'keywords' => 'content'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $faq = $faqService->saveFaqFromArray($values);
                    
                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-faq', 'faq', array('id' => $faq->getId())));
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
    
    public function editFaqAction() {
        $faqService = $this->_service->getService('Faq_Service_Faq');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $categoryService = $this->_service->getService('Faq_Service_Category');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $translator = $this->_service->get('translate');
        
        if(!$faq = $faqService->getFaq($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Faq not found');
        }
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $faqService->getFaqForm($faq);
        $form->getElement('category_id')->setMultiOptions($categoryService->getTargetCategorySelectOptions(true,$adminLanguage->getId()));
        $form->getElement('category_id')->setValue($faq['Category']['id']);
        $metatagsForm = $metatagService->getMetatagsSubForm($faq->get('Metatags'));
        $form->addSubForm($metatagsForm, 'metatags');
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    
                    if($metatags = $metatagService->saveMetatagsFromArray($faq->get('Metatags'), $values, array('title' => 'title', 'description' => 'content', 'keywords' => 'content'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $faq = $faqService->savefaqFromArray($values);
                    
                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();

                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-faq', 'faq'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('languages', $languages);
        $this->view->assign('faq', $faq);
        $this->view->assign('form', $form);
    }
    
    public function removeFaqAction() {
        $faqService = $this->_service->getService('Faq_Service_Faq');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $metatagTranslationService = $this->_service->getService('Default_Service_MetatagTranslation');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if($faq = $faqService->getFaq($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                $metatag = $metatagService->getMetatag((int) $faq->getMetatagId());
                $metatagTranslation = $metatagTranslationService->getMetatagTranslation((int) $faq->getMetatagId());

                $photoRoot = $faq->get('PhotoRoot');
                $photoService->removePhoto($photoRoot);
                
                $faqService->removeFaq($faq);

                $metatagService->removeMetatag($metatag);
                $metatagTranslationService->removeMetatagTranslation($metatagTranslation);

                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-faq', 'faq'));
            } catch(Exception $e) {
                var_dump($e->getMessage());exit;
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-faq', 'faq'));
    }
    
    // ajax actions
    
    public function addFaqPhotoAction() {
        $faqService = $this->_service->getService('Faq_Service_Faq');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$faq = $faqService->getFaq((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Faq not found');
        }
        
        $options = $this->getInvokeArg('bootstrap')->getOptions();
        if(!array_key_exists('domain', $options)) {
            throw new Zend_Controller_Action_Exception('Domain string not set');
        }
        
        $href = $this->getRequest()->getParam('hrefs');

        if(is_string($href) && strlen($href)) {
            $path = str_replace("http://" . $options['domain'], "", urldecode($href));
            $filePath = urldecode($options['publicDir'] . $path);
            if(file_exists($filePath)) {
                $pathinfo = pathinfo($filePath);
                $slug = MF_Text::createSlug($pathinfo['basename']);
                $name = MF_Text::createUniqueFilename($slug, $photoService->photosDir);
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                    $root = $faq->get('PhotoRoot');
                    if(!$root || $root->isInProxyState()) {
                        $photo = $photoService->createPhoto($filePath, $name, $pathinfo['filename'], array_keys(Faq_Model_Doctrine_Faq::getFaqPhotoDimensions()), false, false);
                    } else {
                        $photo = $photoService->clearPhoto($root);
                        $photo = $photoService->updatePhoto($root, $filePath, null, $name, $pathinfo['filename'], array_keys(Faq_Model_Doctrine_Faq::getFaqPhotoDimensions()), false);
                    }

                    $faq->set('PhotoRoot', $photo);
                    $faq->save();

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }

        $list = '';
        
        $faqPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $faq->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $faqPhotos->add($root);
            $list = $this->view->partial('admin/faq-main-photo.phtml', 'faq', array('photos' => $faqPhotos, 'faq' => $faq));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $faq->getId()
        ));
        
    }
    
    public function editFaqPhotoAction() {
        $faqService = $this->_service->getService('Faq_Service_Faq');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $translator = $this->_service->get('translate');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        if(!$faq = $faqService->getFaq((int) $this->getRequest()->getParam('faq-id'))) {
            throw new Zend_Controller_Action_Exception('Faq not found');
        }
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            $this->view->messages()->add($translator->translate('First you have to choose picture'), 'error');
            $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-faq', 'faq', array('id' => $faq->getId())));
        }

        $form = $photoService->getPhotoForm($photo);
        $form->setAction($this->view->adminUrl('edit-faq-photo', 'faq', array('faq-id' => $faq->getId(), 'id' => $photo->getId())));
        
        $photosDir = $photoService->photosDir;
        $offsetDir = realpath($photosDir . DIRECTORY_SEPARATOR . $photo->getOffset());
        if(strlen($photo->getFilename()) > 0 && file_exists($offsetDir . DIRECTORY_SEPARATOR . $photo->getFilename())) {
            list($width, $height) = getimagesize($offsetDir . DIRECTORY_SEPARATOR . $photo->getFilename());
            $this->view->assign('imgDimensions', array('width' => $width, 'height' => $height));
        }
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    $photo = $photoService->saveFromArray($values);

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    if($this->getRequest()->getParam('saveOnly') == '1')
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-faq-photo', 'faq', array('id' => $faq->getId(), 'photo' => $photo->getId())));
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-faq', 'faq', array('id' => $faq->getId())));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('Logger')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('faq', $faq);
        $this->view->assign('photo', $photo);
        $this->view->assign('dimensions', Faq_Model_Doctrine_Faq::getFaqPhotoDimensions());
        $this->view->assign('form', $form);
    }
    
    public function removeFaqPhotoAction() {
        $faqService = $this->_service->getService('Faq_Service_Faq');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$faq = $faqService->getFaq((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Faq not found');
        }
        
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
            if($root = $faq->get('PhotoRoot')) {
                if($root && !$root->isInProxyState()) {
                    $photo = $photoService->updatePhoto($root);
                    $photo->setOffset(null);
                    $photo->setFilename(null);
                    $photo->setTitle(null);
                    $photo->save();
                }
            }
        
            $this->_service->get('doctrine')->getCurrentConnection()->commit();
        } catch(Exception $e) {
            $this->_service->get('doctrine')->getCurrentConnection()->rollback();
            $this->_service->get('log')->log($e->getMessage(), 4);
        }
        
        $list = '';
        
        $faqPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $faq->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $faqPhotos->add($root);
            $list = $this->view->partial('admin/faq-main-photo.phtml', 'faq', array('photos' => $faqPhotos, 'faq' => $faq));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $faq->getId()
        ));
        
    }
    
    public function refreshSponsoredFaqAction() {
        $faqService = $this->_service->getService('Faq_Service_Faq');
        
        if(!$faq = $faqService->getFaq((int) $this->getRequest()->getParam('faq-id'))) {
            throw new Zend_Controller_Action_Exception('Faq not found');
        }
        
        $faqService->refreshSponsoredFaq($faq);
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-faq', 'faq'));
        $this->_helper->viewRenderer->setNoRender();
    }
    
    /* category */
    
     public function listCategoryAction() {

    }
    
    public function listCategoryDataAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $table = Doctrine_Core::getTable('Faq_Model_Doctrine_Category');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Faq_DataTables_Category', 
            'columns' => array('xt.title', 'x.created_at'),
            'searchFields' => array('xt.title')
        ));
        
        $results = $dataTables->getResult();
        
        $language = $i18nService->getAdminLanguage();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();
            //$row['DT_RowId'] = $result->id;
            $row[] = $result->Translation[$language->getId()]->title;
            $options = '<a href="' . $this->view->adminUrl('edit-category', 'faq', array('id' => $result->id)) . '" title="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            $options .= '<a href="' . $this->view->adminUrl('remove-category', 'faq', array('id' => $result->id)) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
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
    
    public function addCategoryAction() {
        $categoryService = $this->_service->getService('Faq_Service_Category');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $translator = $this->_service->get('translate');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $categoryService->getCategoryForm();
        
        $metatagsForm = $metatagService->getMetatagsSubForm();
        $form->addSubForm($metatagsForm, 'metatags');
      

        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
            
                    if($metatags = $metatagService->saveMetatagsFromArray(null, $values, array('title' => 'title', 'description' => 'content', 'keywords' => 'content'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    $category = $categoryService->saveCategoryFromArray($values);
                    
                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-category', 'faq'));
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
    
    public function editCategoryAction() {
        $categoryService = $this->_service->getService('Faq_Service_Category');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        
        $translator = $this->_service->get('translate');
        
        if(!$category = $categoryService->getCategory($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Category not found');
        }
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $categoryService->getCategoryForm($category);
        
        $metatagsForm = $metatagService->getMetatagsSubForm($category->get('Metatags'));
        $form->addSubForm($metatagsForm, 'metatags');
        
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    
                    $category = $categoryService->saveCategoryFromArray($values);
                    
                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();

                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-category', 'faq'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('languages', $languages);
        $this->view->assign('faq', $faq);
        $this->view->assign('form', $form);
    }
    
    public function removeCategoryAction() {
        $categoryService = $this->_service->getService('Faq_Service_Category');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $metatagTranslationService = $this->_service->getService('Default_Service_MetatagTranslation');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if($category = $categoryService->getCategory($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                
                $categoryService->removeCategory($category);


                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-category', 'faq'));
            } catch(Exception $e) {
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-category', 'faq'));
    }
    
    
}

