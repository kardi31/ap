<?php

/**
 * Category
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Faq_Service_Category extends MF_Service_ServiceAbstract {
    
    protected $categoryTable;
    public static $articleItemCountPerPage = 5;
    
    public static function getArticleItemCountPerPage(){
        return self::$articleItemCountPerPage;
    }
    
    public function init() {
        $this->categoryTable = Doctrine_Core::getTable('Faq_Model_Doctrine_Category');
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
    
    public function getCategoryForm(Faq_Model_Doctrine_Category $faq = null) {
        $form = new Faq_Form_Category();
        
        if(null != $faq) {
            $form->populate($faq->toArray());
            
            $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
            $languages = $i18nService->getLanguageList();
            foreach($languages as $language) {
                $i18nSubform = $form->translations->getSubForm($language);
                if($i18nSubform) {
                    $i18nSubform->getElement('title')->setValue($faq->Translation[$language]->title);
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
        if(!$faq = $this->categoryTable->getProxy($values['id'])) {
            $faq = $this->categoryTable->getRecord();
        }

        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        
        
        $faq->fromArray($values);

        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            if(is_array($values['translations'][$language]) && strlen($values['translations'][$language]['title'])) {
                $faq->Translation[$language]->title = $values['translations'][$language]['title'];
                $faq->Translation[$language]->slug = MF_Text::createUniqueTableSlug('Faq_Model_Doctrine_CategoryTranslation', $values['translations'][$language]['title'], $faq->getId());
               
            }
        }
        
        $faq->save();
        
        return $faq;
    }
    
    public function removeCategory(Faq_Model_Doctrine_Category $faq) {
        $faq->delete();
    }
    
//    public function searchCategory($phrase, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
//        $q = $this->categoryTable->getAllCategoryQuery();
//        $q->addSelect('TRIM(at.title) AS search_title, TRIM(at.content) as search_content, "faq" as search_type');
//        $q->andWhere('at.title LIKE ? OR at.content LIKE ?', array("%$phrase%", "%$phrase%"));
//        return $q->execute(array(), $hydrationMode);
//    }
    
    public function getAllSortedCategories($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->categoryTable->getPublishCategoryQuery();
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
    
    public function getPreSortedPredifiniedCategory($faqIds) {
        $q = $this->categoryTable->getAllCategoryQuery();
        $q->where('a.id IN ?', array($faqIds));
        if(is_array($faqIds)):
            $q->addOrderBy('FIELD(a.id, '.implode(', ', $faqIds).')');
        endif; 
        return $q->execute(array(), Doctrine_Core::HYDRATE_ARRAY);  
    }
    
    public function refreshSponsoredCategory($faq){
        if ($faq->isSponsored()):
            $faq->setSponsored(0);
        else:
            $faq->setSponsored(1);
        endif;
        $faq->save();
    }
    
    public function searchCategory($phrase, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->categoryTable->getPublishCategoryQuery();
        $q = $this->categoryTable->getPhotoQuery($q);
        $q->addSelect('TRIM(at.title) AS search_title, TRIM(at.content) as search_content, "faq" as search_type');
        $q->andWhere('at.title LIKE ? OR at.content LIKE ?', array("%$phrase%", "%$phrase%"));
        //$q->addOrderBy('RANDOM()');
        return $q->execute(array(), $hydrationMode);
    }
    
        
    public function getAllCategoriesForSiteMap($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->categoryTable->getPublishCategoryQuery();
        return $q->execute(array(), $hydrationMode);
    }
}

