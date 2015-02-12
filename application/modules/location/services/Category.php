<?php

/**
 * Category
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Location_Service_Category extends MF_Service_ServiceAbstract {
    
    protected $categoryTable;
    public static $articleItemCountPerPage = 5;
    
    public static function getArticleItemCountPerPage(){
        return self::$articleItemCountPerPage;
    }
    
    public function init() {
        $this->categoryTable = Doctrine_Core::getTable('Location_Model_Doctrine_Category');
    }
    
    public function getAllCategories($countOnly = false) {
        if(true == $countOnly) {
            return $this->categoryTable->count();
        } else {
            return $this->categoryTable->findAll();
        }
    }
    
    public function getCategory($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->categoryTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getFullCategory($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->categoryTable->getPublishCategoryQuery();
     //   $q = $this->categoryTable->getPhotoQuery($q);
        if(in_array($field, array('id'))) {
            $q->andWhere('a.' . $field . ' = ?', array($id));
        } elseif(in_array($field, array('slug'))) {
            $q->andWhere('at.' . $field . ' = ?', array($id));
            //$q->andWhere('at.lang = ?', 'pl');
        }
        return $q->fetchOne(array(), $hydrationMode);
    }

    public function getRecentCategory($limit, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->categoryTable->getPublishCategoryQuery();
        $q = $this->categoryTable->getPhotoQuery($q);
        $q = $this->categoryTable->getLimitQuery($limit, $q);
        $q->orderBy('a.created_at DESC');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getNew($limit, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->categoryTable->getPublishCategoryQuery();
        $q = $this->categoryTable->getPhotoQuery($q);
        $q = $this->categoryTable->getLimitQuery($limit, $q);
        $q->orderBy('a.created_at DESC');
        return $q->execute(array(), $hydrationMode);
    }

    public function getCategoryPaginationQuery($language) {
        $q = $this->categoryTable->getPublishCategoryQuery();
        $q = $this->categoryTable->getPhotoQuery($q);
        $q->andWhere('at.lang = ?', $language);
        $q->addOrderBy('a.sponsored DESC');
        $q->addOrderBy('a.publish_date DESC');
        return $q;
    }
    
    public function getCategoryOnMainPage($limit, $language, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->categoryTable->getPublishCategoryQuery();
        $q = $this->categoryTable->getPhotoQuery($q);
        $q->andWhere('at.lang = ?', $language);
        $q->addOrderBy('a.sponsored DESC');
        $q->addOrderBy('a.publish_date DESC');
        $q->limit($limit);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getCategoryForm(Location_Model_Doctrine_Category $location = null) {
        $form = new Location_Form_Category();
        
        if(null != $location) {
            $form->populate($location->toArray());
            
            $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
            $languages = $i18nService->getLanguageList();
            foreach($languages as $language) {
                $i18nSubform = $form->translations->getSubForm($language);
                if($i18nSubform) {
                    $i18nSubform->getElement('title')->setValue($location->Translation[$language]->title);
                }
            }
        }
        
        return $form;
    }
    
    public function saveCategoryFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$category = $this->categoryTable->getProxy($values['id'])) {
            $category = $this->categoryTable->getRecord();
        }

        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        
        $category->fromArray($values);

        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            if(is_array($values['translations'][$language]) && strlen($values['translations'][$language]['title'])) {
                $category->Translation[$language]->title = $values['translations'][$language]['title'];
                $category->Translation[$language]->slug = MF_Text::createUniqueTableSlug('Location_Model_Doctrine_CategoryTranslation', $values['translations'][$language]['title'], $category->getId());
               
            }
        }
        $category->save();
        
        return $category;
    }
    
    public function removeCategory(Location_Model_Doctrine_Category $location) {
        $location->delete();
    }
    
//    public function searchCategory($phrase, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
//        $q = $this->categoryTable->getAllCategoryQuery();
//        $q->addSelect('TRIM(at.title) AS search_title, TRIM(at.content) as search_content, "location" as search_type');
//        $q->andWhere('at.title LIKE ? OR at.content LIKE ?', array("%$phrase%", "%$phrase%"));
//        return $q->execute(array(), $hydrationMode);
//    }
    
    public function getAllSortedCategories($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->categoryTable->createQuery('a');
        $q->orderBy('a.created_at DESC');
        return $q->execute(array(), $hydrationMode);
    }
    
    
     public function getTargetCategorySelectOptions($prependEmptyValue = false, $language = null) {
        $items = $this->getAllSortedCategories(Doctrine_Core::HYDRATE_RECORD);
        $result = array();
        if($prependEmptyValue) {
            $result[''] = ' ';
        }

        foreach($items as $item) {
                $result[$item->getId()] = $item->Translation[$language]->title;
        }
        
        return $result;
    }
    
    public function getPreSortedPredifiniedCategory($locationIds) {
        $q = $this->categoryTable->getAllCategoryQuery();
        $q->where('a.id IN ?', array($locationIds));
        if(is_array($locationIds)):
            $q->addOrderBy('FIELD(a.id, '.implode(', ', $locationIds).')');
        endif; 
        return $q->execute(array(), Doctrine_Core::HYDRATE_ARRAY);  
    }
    
    public function refreshSponsoredCategory($location){
        if ($location->isSponsored()):
            $location->setSponsored(0);
        else:
            $location->setSponsored(1);
        endif;
        $location->save();
    }
    
    public function searchCategory($phrase, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->categoryTable->getPublishCategoryQuery();
        $q = $this->categoryTable->getPhotoQuery($q);
        $q->addSelect('TRIM(at.title) AS search_title, TRIM(at.content) as search_content, "location" as search_type');
        $q->andWhere('at.title LIKE ? OR at.content LIKE ?', array("%$phrase%", "%$phrase%"));
        //$q->addOrderBy('RANDOM()');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getAllCategoryForSiteMap($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->categoryTable->getPublishCategoryQuery();
        $q = $this->categoryTable->getPhotoQuery($q);
        return $q->execute(array(), $hydrationMode);
    }
}

