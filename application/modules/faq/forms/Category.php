<?php

/**
 * News_Form_Category
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Faq_Form_Category extends Admin_Form {
    
    public function init() {
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
  
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
            $title->setLabel('Name');
            $title->setDecorators(self::$textDecorators);
            $title->setAttrib('class', 'span8');
            
            $translationForm->setElements(array(
                $title
            ));

            $translations->addSubForm($translationForm, $language);
        }
        
        $this->addSubForm($translations, 'translations');

        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $id,
            $submit
        ));
    }
}

