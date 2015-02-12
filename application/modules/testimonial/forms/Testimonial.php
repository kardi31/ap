<?php

/**
 * Testimonial_Form_Testimonial
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Testimonial_Form_Testimonial extends Admin_Form {
    
    public function init() {
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $parentId = $this->createElement('hidden', 'parent_id');
        $parentId->setDecorators(self::$hiddenDecorators);
         
        $languages = $i18nService->getLanguageList();

        $translations = new Zend_Form_SubForm();

        foreach($languages as $language) {
            $translationForm = new Zend_Form_SubForm();
            $translationForm->setName($language);
            $translationForm->setDecorators(array(
                'FormElements'
            ));
            
            $description = $translationForm->createElement('textarea', 'description');
            $description->setBelongsTo($language);
            $description->setLabel('Description');
            $description->setDecorators(self::$tinymceDecorators);
            $description->setAttrib('class', 'span8 tinymce');
            
            $translationForm->setElements(array(
                $description
            ));

            $translations->addSubForm($translationForm, $language);
        }

        $this->addSubForm($translations, 'translations');
        
        $authorName = $this->createElement('text', 'author_name');
        $authorName->setLabel('Author name');
        $authorName->setRequired();
        $authorName->setDecorators(self::$textDecorators);
        $authorName->setAttrib('class', 'span8');
        
        $city = $this->createElement('text', 'city');
        $city->setLabel('City');
        $city->setRequired();
        $city->setDecorators(self::$textDecorators);
        $city->setAttrib('class', 'span8');
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $id,
            $parentId,
            $authorName,
            $city,
            $submit
        ));
    }
}

