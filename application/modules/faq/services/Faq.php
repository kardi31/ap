<?php

/**
 * Faq
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Faq_Service_Faq extends MF_Service_ServiceAbstract {
    
    protected $faqTable;
    public static $articleItemCountPerPage = 5;
    
    public static function getArticleItemCountPerPage(){
        return self::$articleItemCountPerPage;
    }
    
    public function init() {
        $this->faqTable = Doctrine_Core::getTable('Faq_Model_Doctrine_Faq');
    }
    
    public function getAllFaq($countOnly = false) {
        if(true == $countOnly) {
            return $this->faqTable->count();
        } else {
            return $this->faqTable->findAll();
        }
    }
    
    public function getFaq($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->faqTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getFullFaq($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->faqTable->getPublishFaqQuery();
        $q = $this->faqTable->getPhotoQuery($q);
        if(in_array($field, array('id'))) {
            $q->andWhere('a.' . $field . ' = ?', array($id));
        } elseif(in_array($field, array('slug'))) {
            $q->andWhere('at.' . $field . ' = ?', array($id));
            //$q->andWhere('at.lang = ?', 'pl');
        }
        return $q->fetchOne(array(), $hydrationMode);
    }

    public function getRecentFaq($limit, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->faqTable->getPublishFaqQuery();
        $q = $this->faqTable->getPhotoQuery($q);
        $q = $this->faqTable->getLimitQuery($limit, $q);
        $q->orderBy('a.created_at DESC');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getNew($limit, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->faqTable->getPublishFaqQuery();
        $q = $this->faqTable->getPhotoQuery($q);
        $q = $this->faqTable->getLimitQuery($limit, $q);
        $q->orderBy('a.created_at DESC');
        return $q->execute(array(), $hydrationMode);
    }

    public function getFaqPaginationQuery($language) {
        $q = $this->faqTable->getPublishFaqQuery();
        $q = $this->faqTable->getPhotoQuery($q);
        $q->andWhere('at.lang = ?', $language);
        $q->addOrderBy('a.sponsored DESC');
        $q->addOrderBy('a.publish_date DESC');
        return $q;
    }
    
    public function getFaqOnMainPage($limit, $language, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->faqTable->getPublishFaqQuery();
        $q = $this->faqTable->getPhotoQuery($q);
        $q->andWhere('at.lang = ?', $language);
        $q->addOrderBy('a.sponsored DESC');
        $q->addOrderBy('a.publish_date DESC');
        $q->limit($limit);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getFaqForm(Faq_Model_Doctrine_Faq $faq = null) {
        $form = new Faq_Form_Faq();
        $form->setDefault('publish', 1);
        
        if(null != $faq) {
            $form->populate($faq->toArray());
           
            
            $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
            $languages = $i18nService->getLanguageList();
            foreach($languages as $language) {
                $i18nSubform = $form->translations->getSubForm($language);
                if($i18nSubform) {
                    $i18nSubform->getElement('title')->setValue($faq->Translation[$language]->title);
                    $i18nSubform->getElement('content')->setValue($faq->Translation[$language]->content);
                }
            }
        }
        
        return $form;
    }
    
    public function saveFaqFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$faq = $this->faqTable->getProxy($values['id'])) {
            $faq = $this->faqTable->getRecord();
        }

        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        
        
        
        $faq->fromArray($values);
        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            if(is_array($values['translations'][$language]) && strlen($values['translations'][$language]['title'])) {
                $faq->Translation[$language]->title = $values['translations'][$language]['title'];
                $faq->Translation[$language]->slug = MF_Text::createUniqueTableSlug('Faq_Model_Doctrine_FaqTranslation', $values['translations'][$language]['title'], $faq->getId());
                $faq->Translation[$language]->content = $values['translations'][$language]['content'];
            }
        }
        
        $faq->save();
        
        return $faq;
    }
    
    public function removeFaq(Faq_Model_Doctrine_Faq $faq) {
        $faq->delete();
    }
    
//    public function searchFaq($phrase, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
//        $q = $this->faqTable->getAllFaqQuery();
//        $q->addSelect('TRIM(at.title) AS search_title, TRIM(at.content) as search_content, "faq" as search_type');
//        $q->andWhere('at.title LIKE ? OR at.content LIKE ?', array("%$phrase%", "%$phrase%"));
//        return $q->execute(array(), $hydrationMode);
//    }
    
    public function getAllSortedFaq($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->faqTable->getPublishFaqQuery();
        $q = $this->faqTable->getPhotoQuery($q);
        $q->orderBy('a.created_at DESC');
        return $q->execute(array(), $hydrationMode);
    }
    
    
     public function getTargetFaqSelectOptions($prependEmptyValue = false, $language = null) {
        $items = $this->getAllSortedFaq(Doctrine_Core::HYDRATE_RECORD);
        $result = array();
        if($prependEmptyValue) {
            $result[''] = ' ';
        }

        foreach($items as $item) {
                $result[$item->getId()] = $item->Translation[$language]->title;
        }
        
        return $result;
    }
    
    public function getPreSortedPredifiniedFaq($faqIds) {
        $q = $this->faqTable->getAllFaqQuery();
        $q->where('a.id IN ?', array($faqIds));
        if(is_array($faqIds)):
            $q->addOrderBy('FIELD(a.id, '.implode(', ', $faqIds).')');
        endif; 
        return $q->execute(array(), Doctrine_Core::HYDRATE_ARRAY);  
    }
    
    public function refreshSponsoredFaq($faq){
        if ($faq->isSponsored()):
            $faq->setSponsored(0);
        else:
            $faq->setSponsored(1);
        endif;
        $faq->save();
    }
    
    public function searchFaq($phrase, $hydrationMode = Doctrine_Core::HYDRATE_RECORD){
        $q = $this->faqTable->getPublishFaqQuery();
        $q = $this->faqTable->getPhotoQuery($q);
        $q->addSelect('TRIM(at.title) AS search_title, TRIM(at.content) as search_content, "faq" as search_type');
        $q->andWhere('at.title LIKE ? OR at.content LIKE ?', array("%$phrase%", "%$phrase%"));
        //$q->addOrderBy('RANDOM()');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getAllFaqForSiteMap($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->faqTable->getPublishFaqQuery();
        $q = $this->faqTable->getPhotoQuery($q);
        return $q->execute(array(), $hydrationMode);
    }
}

