<?php

/**
 * Location_Form_Location
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Location_Form_Location extends Admin_Form {
    
    public function init() {
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $categoryId = $this->createElement('select', 'category_id');
        $categoryId->setLabel('Category');
        $categoryId->setDecorators(self::$selectDecorators);
        
         $cordX = $this->createElement('hidden', 'lat');
        $cordX->setDecorators(array('ViewHelper'));
        
        $cordY = $this->createElement('hidden', 'lng');
        $cordY->setDecorators(array('ViewHelper'));
        
        $languages = $i18nService->getLanguageList();

        $translations = new Zend_Form_SubForm();

        foreach($languages as $language) {
            $translationForm = new Zend_Form_SubForm();
            $translationForm->setName($language);
            $translationForm->setDecorators(array(
                'FormElements'
            ));

            $title = $translationForm->createElement('text', 'title');
            $title->setBelongsTo($language);
            $title->setLabel('Title');
            $title->setDecorators(self::$textDecorators);
            $title->setAttrib('class', 'span8');
            
            $content = $translationForm->createElement('textarea', 'content');
            $content->setBelongsTo($language);
            $content->setLabel('Content');
            $content->setDecorators(self::$tinymceDecorators);
            $content->setAttrib('class', 'span8 tinymce');
            
            $city = $translationForm->createElement('text', 'city');
            $city->setBelongsTo($language);
            $city->setLabel('City');
            $city->setDecorators(self::$textDecorators);
            $city->setAttrib('class', 'city span8');
            
            $translationForm->setElements(array(
                $title,
                $content,
                $city
            ));

            $translations->addSubForm($translationForm, $language);
        }
        
        $this->addSubForm($translations, 'translations');

       
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $id,
            $cordX,
            $cordY,
            $categoryId,
            $submit
        ));
    }
}

