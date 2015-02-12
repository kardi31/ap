<?php

/**
 * Location_AdminController
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Location_AdminController extends MF_Controller_Action {
    
    public static $itemCountPerPage = 15;
    
    public function listLocationAction() {
    }
    
    public function listLocationDataAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $table = Doctrine_Core::getTable('Location_Model_Doctrine_Location');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Location_DataTables_Location', 
            'columns' => array('xt.title'),
            'searchFields' => array('xt.title')
        ));
        
        $results = $dataTables->getResult();
        
        $language = $i18nService->getAdminLanguage();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row[] = $result->Translation[$language->getId()]->title;
            $row[] = $result->Translation[$language->getId()]->city;
            $row[] = MF_Text::timeFormat($result->created_at, 'H:i d/m/Y');
           $options = '<a href="' . $this->view->adminUrl('edit-location', 'location', array('id' => $result->id)) . '" title="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            $options .= '<a href="' . $this->view->adminUrl('remove-location', 'location', array('id' => $result->id)) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
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
    
    public function addLocationAction() {
        $locationService = $this->_service->getService('Location_Service_Location');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $categoryService = $this->_service->getService('Location_Service_Category');
        
        $translator = $this->_service->get('translate');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $locationService->getLocationForm();
        
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
                    
                    $location = $locationService->saveLocationFromArray($values);
                    
                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-location', 'location', array('id' => $location->getId())));
                } catch(Exception $e) {
                    var_dump($e->getMessage());exit;
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
    
    public function editLocationAction() {
        $locationService = $this->_service->getService('Location_Service_Location');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $categoryService = $this->_service->getService('Location_Service_Category');
        
        
        
        $translator = $this->_service->get('translate');
        
        if(!$location = $locationService->getLocation($this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Location not found');
        }
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        $form = $locationService->getLocationForm($location);
        
        $metatagsForm = $metatagService->getMetatagsSubForm($location->get('Metatags'));
        $form->addSubForm($metatagsForm, 'metatags');
        
        $form->getElement('category_id')->setMultiOptions($categoryService->getTargetCategorySelectOptions(true,$adminLanguage->getId()));
        $form->getElement('category_id')->setValue($location['Category']['id']);
                
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getParams())) {
                try {
                    $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
                    $values = $form->getValues();
                    
                    if($metatags = $metatagService->saveMetatagsFromArray($location->get('Metatags'), $values, array('title' => 'title', 'description' => 'content', 'keywords' => 'content'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    
                    $location = $locationService->savelocationFromArray($values);
                    
                    $this->view->messages()->add($translator->translate('Item has been updated'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();

                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-location', 'location'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('languages', $languages);
        $this->view->assign('location', $location);
        $this->view->assign('form', $form);
    }
    
    public function removeLocationAction() {
        $locationService = $this->_service->getService('Location_Service_Location');
        $metatagService = $this->_service->getService('Default_Service_Metatag');
        $metatagTranslationService = $this->_service->getService('Default_Service_MetatagTranslation');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if($location = $locationService->getLocation($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                $metatag = $metatagService->getMetatag((int) $location['metatag_id']);

                $photoRoot = $location->get('PhotoRoot');
                $photoService->removePhoto($photoRoot);
                
                $locationService->removeLocation($location);

                $metatagService->removeMetatag($metatag);

                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-location', 'location'));
            } catch(Exception $e) {
                var_dump($e->getMessage());exit;
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-location', 'location'));
    }
    
    // ajax actions
    
    public function addLocationPhotoAction() {
        $locationService = $this->_service->getService('Location_Service_Location');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$location = $locationService->getLocation((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Location not found');
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

                    $root = $location->get('PhotoRoot');
                    if(!$root || $root->isInProxyState()) {
                        $photo = $photoService->createPhoto($filePath, $name, $pathinfo['filename'], array_keys(Location_Model_Doctrine_Location::getLocationPhotoDimensions()), false, false);
                    } else {
                        $photo = $photoService->clearPhoto($root);
                        $photo = $photoService->updatePhoto($root, $filePath, null, $name, $pathinfo['filename'], array_keys(Location_Model_Doctrine_Location::getLocationPhotoDimensions()), false);
                    }

                    $location->set('PhotoRoot', $photo);
                    $location->save();

                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }

        $list = '';
        
        $locationPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $location->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $locationPhotos->add($root);
            $list = $this->view->partial('admin/location-main-photo.phtml', 'location', array('photos' => $locationPhotos, 'location' => $location));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $location->getId()
        ));
        
    }
    
    public function editLocationPhotoAction() {
        $locationService = $this->_service->getService('Location_Service_Location');
        $photoService = $this->_service->getService('Media_Service_Photo');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $translator = $this->_service->get('translate');
        
        $adminLanguage = $i18nService->getAdminLanguage();
        
        if(!$location = $locationService->getLocation((int) $this->getRequest()->getParam('location-id'))) {
            throw new Zend_Controller_Action_Exception('Location not found');
        }
        
        if(!$photo = $photoService->getPhoto((int) $this->getRequest()->getParam('id'))) {
            $this->view->messages()->add($translator->translate('First you have to choose picture'), 'error');
            $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-location', 'location', array('id' => $location->getId())));
        }

        $form = $photoService->getPhotoForm($photo);
        $form->setAction($this->view->adminUrl('edit-location-photo', 'location', array('location-id' => $location->getId(), 'id' => $photo->getId())));
        
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
                        $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-location-photo', 'location', array('id' => $location->getId(), 'photo' => $photo->getId())));
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('edit-location', 'location', array('id' => $location->getId())));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('Logger')->log($e->getMessage(), 4);
                }
            }
        }
        
        $this->view->assign('location', $location);
        $this->view->assign('photo', $photo);
        $this->view->assign('dimensions', Location_Model_Doctrine_Location::getLocationPhotoDimensions());
        $this->view->assign('form', $form);
    }
    
    public function removeLocationPhotoAction() {
        $locationService = $this->_service->getService('Location_Service_Location');
        $photoService = $this->_service->getService('Media_Service_Photo');
        
        if(!$location = $locationService->getLocation((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Location not found');
        }
        
        try {
            $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                    
            if($root = $location->get('PhotoRoot')) {
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
        
        $locationPhotos = new Doctrine_Collection('Media_Model_Doctrine_Photo');
        $root = $location->get('PhotoRoot');
        if($root && !$root->isInProxyState()) {
            $locationPhotos->add($root);
            $list = $this->view->partial('admin/location-main-photo.phtml', 'location', array('photos' => $locationPhotos, 'location' => $location));
        }
        
        $this->_helper->json(array(
            'status' => 'success',
            'body' => $list,
            'id' => $location->getId()
        ));
        
    }
    
    public function refreshSponsoredLocationAction() {
        $locationService = $this->_service->getService('Location_Service_Location');
        
        if(!$location = $locationService->getLocation((int) $this->getRequest()->getParam('location-id'))) {
            throw new Zend_Controller_Action_Exception('Location not found');
        }
        
        $locationService->refreshSponsoredLocation($location);
        
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-location', 'location'));
        $this->_helper->viewRenderer->setNoRender();
    }
    
    /* category */
      public function listCategoryAction() {

    }
    
    public function listCategoryDataAction() {
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $table = Doctrine_Core::getTable('Location_Model_Doctrine_Category');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Location_DataTables_Category', 
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
            $options = '<a href="' . $this->view->adminUrl('edit-category', 'location', array('id' => $result->id)) . '" title="' . $this->view->translate('Edit') . '"><span class="icon24 entypo-icon-settings"></span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            $options .= '<a href="' . $this->view->adminUrl('remove-category', 'location', array('id' => $result->id)) . '" class="remove" title="' . $this->view->translate('Remove') . '"><span class="icon16 icon-remove"></span></a>';
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
        $categoryService = $this->_service->getService('Location_Service_Category');
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
            
                    if($metatags = $metatagService->saveMetatagsFromArray(null, $values, array('title' => 'title'))) {
                        $values['metatag_id'] = $metatags->getId();
                    }
                    $category = $categoryService->saveCategoryFromArray($values);
                    
                    $this->view->messages()->add($translator->translate('Item has been added'), 'success');
                    
                    $this->_service->get('doctrine')->getCurrentConnection()->commit();
                    
                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-category', 'location'));
                } catch(Exception $e) {
                    var_dump($e->getMessage());exit;
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
        $categoryService = $this->_service->getService('Location_Service_Category');
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

                    $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-category', 'location'));
                } catch(Exception $e) {
                    $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                    $this->_service->get('log')->log($e->getMessage(), 4);
                }
            }
        }
        
        $languages = $i18nService->getLanguageList();
        
        $this->view->assign('adminLanguage', $adminLanguage);
        $this->view->assign('languages', $languages);
        $this->view->assign('location', $location);
        $this->view->assign('form', $form);
    }
    
    public function removeCategoryAction() {
        $categoryService = $this->_service->getService('Location_Service_Category');
        
        if($category = $categoryService->getCategory($this->getRequest()->getParam('id'))) {
            try {
                $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();

                
                $categoryService->removeCategory($category);


                $this->_service->get('doctrine')->getCurrentConnection()->commit();
                $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-category', 'location'));
            } catch(Exception $e) {
                $this->_service->get('Logger')->log($e->getMessage(), 4);
            }
        }
        $this->_helper->redirector->gotoUrl($this->view->adminUrl('list-category', 'location'));
    }
}

