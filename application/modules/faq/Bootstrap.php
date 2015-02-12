<?php

/**
 * Bootstrap
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Faq_Bootstrap extends Zend_Application_Module_Bootstrap {
    
    protected function _initModuleAutoloader() {
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'basePath' => APPLICATION_PATH . '/modules/faq',
            'namespace' => '',
            'resourceTypes' => array(
                'library' => array(
                    'path' => 'library/',
                    'namespace' => 'Faq'
                )
            )
        ));
    }
    
    protected function _initModel() {
        Doctrine_Core::loadModels(APPLICATION_PATH . '/modules/faq/models/Doctrine', Doctrine_Core::MODEL_LOADING_CONSERVATIVE, $this->getModuleName() . '_Model_Doctrine_');
    }
	
}

