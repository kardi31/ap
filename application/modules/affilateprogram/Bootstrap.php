<?php

/**
 * Bootstrap
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Affilateprogram_Bootstrap extends Zend_Application_Module_Bootstrap {
    
    protected function _initModuleAutoloader() {
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'basePath' => APPLICATION_PATH . '/modules/affilateprogram',
            'namespace' => '',
            'resourceTypes' => array(
                'library' => array(
                    'path' => 'library/',
                    'namespace' => 'Affilateprogram'
                )
            )
        ));
    }
    
    protected function _initModel() {
        Doctrine_Core::loadModels(APPLICATION_PATH . '/modules/affilateprogram/models/Doctrine', Doctrine_Core::MODEL_LOADING_CONSERVATIVE, $this->getModuleName() . '_Model_Doctrine_');
    }
	
}

