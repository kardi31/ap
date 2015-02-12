<?php

/**
 * Testimonial_Service_Testimonial
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Testimonial_Service_Testimonial extends MF_Service_ServiceAbstract {
    
    protected $testimonialTable;
    public static $testimonialItemCountPerPage = 5;
    
    public static function getTestimonialItemCountPerPage(){
        return self::$testimonialItemCountPerPage;
    }
    
    public function init() {
        $this->testimonialTable = Doctrine_Core::getTable('Testimonial_Model_Doctrine_Testimonial');
    }
      
    public function getTestimonial($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->testimonialTable->findOneBy($field, $id, $hydrationMode);
    }
   
    public function getTestimonialForm(Testimonial_Model_Doctrine_Testimonial $testimonial = null) {
        $form = new Testimonial_Form_Testimonial();
        
        if(null != $testimonial) {
            $form->populate($testimonial->toArray());
            
            $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
            $languages = $i18nService->getLanguageList();
            foreach($languages as $language) {
                $i18nSubform = $form->translations->getSubForm($language);
                if($i18nSubform) {;
                    $i18nSubform->getElement('description')->setValue($testimonial->Translation[$language]->description);
                }
            }
        }   
        return $form;
    }
    
    public function saveTestimonialFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$testimonial = $this->testimonialTable->getProxy($values['id'])) {
            $testimonial = $this->testimonialTable->getRecord();
        }
        
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        $testimonial->fromArray($values);
        $languages = $i18nService->getLanguageList();
        foreach($languages as $language) {
            if(is_array($values['translations'][$language])) {
                $testimonial->Translation[$language]->description = $values['translations'][$language]['description'];
            }
        }
        
        $testimonial->save();
        
        if(isset($values['parent_id'])) {
            $parent = $this->getTestimonial((int) $values['parent_id']);
            $testimonial->getNode()->insertAsLastChildOf($parent);
        }
        
        return $testimonial;
    }
    
    public function getTestimonialTree() {
        return $this->testimonialTable->getTree();
    }
    
    public function getTestimonialRoot() {
        return $this->getTestimonialTree()->fetchRoot();
    }
    
    public function createTestimonialRoot() {
        $testimonial = $this->testimonialTable->getRecord();
        $testimonial->save();
        $tree = $this->getTestimonialTree();
        $tree->createRoot($testimonial);
        return $testimonial;
    }    
    
    public function moveTestimonial($testimonial, $mode) {
        switch($mode) {
            case 'up':
                $prevSibling = $testimonial->getNode()->getPrevSibling();
                if($prevSibling->getNode()->isRoot()) {
                    throw new Exception('Cannot move category on root level');
                }
                $testimonial->getNode()->moveAsPrevSiblingOf($prevSibling);
                break;
            case 'down':
                $nextSibling = $testimonial->getNode()->getNextSibling();
                if($nextSibling->getNode()->isRoot()) {
                    throw new Exception('Cannot move category on root level');
                }
                $testimonial->getNode()->moveAsNextSiblingOf($nextSibling);
                break;
        }
    }
    
    public function getPublicTestimonials($language, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->testimonialTable->getTestimonialQuery();
        $q->andWhere('tt.lang = ?', $language);
        $q->andWhere('t.status = ?', 1);
        $q->addOrderBy('t.lft ASC');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getTestimonialsPaginationQuery($language) {
        $q = $this->testimonialTable->getTestimonialQuery();
        $q->andWhere('tt.lang = ?', $language);
        $q->andWhere('t.status = ?', 1);
        $q->addOrderBy('t.lft ASC');
        return $q;
    }
    
    public function removeTestimonial(Testimonial_Model_Doctrine_Testimonial $testimonial) {
        $testimonial->getNode()->delete();
        $testimonial->delete();
    }
    
    public function refreshStatusTestimonial($testimonial){
        if ($testimonial->isStatus()):
            $testimonial->setStatus(0);
        else:
            $testimonial->setStatus(1);
        endif;
        $testimonial->save();
    }
}

