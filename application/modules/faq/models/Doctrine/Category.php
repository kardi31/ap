<?php

/**
 * Faq_Model_Doctrine_Category
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    Admi
 * @subpackage Faq
 * @author     Michał Folga <michalfolga@gmail.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Faq_Model_Doctrine_Category extends Faq_Model_Doctrine_BaseCategory
{
    public function getId(){
        return $this->_get('id');
    }
    
     public function setUp() {
        parent::setUp();
    
        $this->hasOne('Default_Model_Doctrine_Metatag as Metatags', array(
            'local' => 'metatag_id',
            'foregin' => 'id'
        ));
    }
}