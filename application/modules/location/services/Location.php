<?php

/**
 * Location
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Location_Service_Location extends MF_Service_ServiceAbstract {
    
    protected $locationTable;
    public static $articleItemCountPerPage = 5;
    
    public static function getArticleItemCountPerPage(){
        return self::$articleItemCountPerPage;
    }
    
    public function init() {
        $this->locationTable = Doctrine_Core::getTable('Location_Model_Doctrine_Location');
    }
    
    public function getAllLocation($countOnly = false) {
        if(true == $countOnly) {
            return $this->locationTable->count();
        } else {
            return $this->locationTable->findAll();
        }
    }
    
    public function getLocation($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->locationTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getFullLocation($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->locationTable->getPublishLocationQuery();
       // $q = $this->locationTable->getPhotoQuery($q);
        if(in_array($field, array('id'))) {
            $q->andWhere('l.' . $field . ' = ?', array($id));
        } elseif(in_array($field, array('slug'))) {
            $q->andWhere('lt.' . $field . ' = ?', array($id));
            //$q->andWhere('at.lang = ?', 'pl');
        }
        return $q->fetchOne(array(), $hydrationMode);
    }

    public function getRecentLocation($limit, $categoryIds, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->locationTable->getPublishLocationQuery();
        $q = $this->locationTable->getPhotoQuery($q);
        $q = $this->locationTable->getLimitQuery($limit, $q);
        $q = $this->locationTable->getCategoryIdQuery($categoryIds, $q);
        $q->orderBy('a.created_at DESC');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getLocationsForJson($language = 'en', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->locationTable->createQuery('l');
        $q->leftJoin('l.Translation lt');
        $q->addSelect('lt.city,lt.id,l.lat,l.lng');
        $q->addWhere('lt.lang = ?',$language);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getAlli18nLocations($order = 'lt.title DESC',$language = 'en', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->locationTable->getPublishLocationQuery();
        $q->addWhere('lt.lang = ?',$language);
        $q->orderBy($order);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getNew($limit, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->locationTable->getPublishLocationQuery();
        $q = $this->locationTable->getPhotoQuery($q);
        $q = $this->locationTable->getLimitQuery($limit, $q);
        $q->orderBy('a.created_at DESC');
        return $q->execute(array(), $hydrationMode);
    }

    public function getLocationPaginationQuery($language) {
        $q = $this->locationTable->getPublishLocationQuery();
        $q = $this->locationTable->getPhotoQuery($q);
        $q->andWhere('at.lang = ?', $language);
        $q->addOrderBy('a.sponsored DESC');
        $q->addOrderBy('a.publish_date DESC');
        return $q;
    }
    
    public function getLocationOnMainPage($limit, $language, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->locationTable->getPublishLocationQuery();
        $q = $this->locationTable->getPhotoQuery($q);
        $q->andWhere('at.lang = ?', $language);
        $q->addOrderBy('a.sponsored DESC');
        $q->addOrderBy('a.publish_date DESC');
        $q->limit($limit);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getLocationForm(Location_Model_Doctrine_Location $location = null) {
        $form = new Location_Form_Location();
        $form->setDefault('publish', 1);
        
        if(null != $location) {
            $form->populate($location->toArray());
            
            
            $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
            $languages = $i18nService->getLanguageList();
            foreach($languages as $language) {
                $i18nSubform = $form->translations->getSubForm($language);
                if($i18nSubform) {
                    $i18nSubform->getElement('title')->setValue($location->Translation[$language]->title);
                    $i18nSubform->getElement('content')->setValue($location->Translation[$language]->content);
                    $i18nSubform->getElement('city')->setValue($location->Translation[$language]->city);
                }
            }
        }
        
        return $form;
    }
    
    public function saveLocationFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$location = $this->locationTable->getProxy($values['id'])) {
            $location = $this->locationTable->getRecord();
        }

        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        
        
        $location->fromArray($values);

        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            if(is_array($values['translations'][$language]) && strlen($values['translations'][$language]['title'])) {
                $location->Translation[$language]->title = $values['translations'][$language]['title'];
                $location->Translation[$language]->slug = MF_Text::createUniqueTableSlug('Location_Model_Doctrine_LocationTranslation', $values['translations'][$language]['title'], $location->getId());
                $location->Translation[$language]->content = $values['translations'][$language]['content'];
                $location->Translation[$language]->city = $values['translations'][$language]['city'];
            }
        }
        
        $location->save();
        
        return $location;
    }
    
    public function removeLocation(Location_Model_Doctrine_Location $location) {
        $location->delete();
    }
    
//    public function searchLocation($phrase, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
//        $q = $this->locationTable->getAllLocationQuery();
//        $q->addSelect('TRIM(at.title) AS search_title, TRIM(at.content) as search_content, "location" as search_type');
//        $q->andWhere('at.title LIKE ? OR at.content LIKE ?', array("%$phrase%", "%$phrase%"));
//        return $q->execute(array(), $hydrationMode);
//    }
    
    public function getAllSortedLocation($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->locationTable->getPublishLocationQuery();
        $q = $this->locationTable->getPhotoQuery($q);
        $q->orderBy('a.created_at DESC');
        return $q->execute(array(), $hydrationMode);
    }
    
    
     public function getTargetLocationSelectOptions($prependEmptyValue = false, $language = null) {
        $items = $this->getAllSortedLocation(Doctrine_Core::HYDRATE_RECORD);
        $result = array();
        if($prependEmptyValue) {
            $result[''] = ' ';
        }

        foreach($items as $item) {
                $result[$item->getId()] = $item->Translation[$language]->title;
        }
        
        return $result;
    }
    
    public function getPreSortedPredifiniedLocation($locationIds) {
        $q = $this->locationTable->getAllLocationQuery();
        $q->where('a.id IN ?', array($locationIds));
        if(is_array($locationIds)):
            $q->addOrderBy('FIELD(a.id, '.implode(', ', $locationIds).')');
        endif; 
        return $q->execute(array(), Doctrine_Core::HYDRATE_ARRAY);  
    }
    
    public function refreshSponsoredLocation($location){
        if ($location->isSponsored()):
            $location->setSponsored(0);
        else:
            $location->setSponsored(1);
        endif;
        $location->save();
    }
    
    public function searchLocation($phrase, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->locationTable->getPublishLocationQuery();
        $q = $this->locationTable->getPhotoQuery($q);
        $q->addSelect('TRIM(at.title) AS search_title, TRIM(at.content) as search_content, "location" as search_type');
        $q->andWhere('at.title LIKE ? OR at.content LIKE ?', array("%$phrase%", "%$phrase%"));
        //$q->addOrderBy('RANDOM()');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getAllLocationsForSiteMap($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->locationTable->getPublishLocationQuery();
        return $q->execute(array(), $hydrationMode);
    }
}

