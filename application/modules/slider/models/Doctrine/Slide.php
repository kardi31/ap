<?php

/**
 * Slider_Model_Doctrine_Slide
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    Admi
 * @subpackage Slider
 * @author     Andrzej Wilczyński <and.wilczynski@gmail.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Slider_Model_Doctrine_Slide extends Slider_Model_Doctrine_BaseSlide
{
    public static $slidePhotoDimensions = array(
        '126x126' => 'Admin slide list',
        '500x223' => 'Thumbnail',
        '1680x579' => 'Main photo'
    );
    
    public static function getSlidePhotoDimensions() {
        return self::$slidePhotoDimensions;
    }
    
    public function setId($id) {
        $this->_set('id', $id);
    }
    
    public function getId() {
        return $this->_get('id');
    }
    
    public function setSliderId($sliderId) {
        $this->_set('slider_id', $sliderId);
    }
    
    public function getSliderId() {
        return $this->_get('slider_id');
    }
    
    public function setTitle($title) {
        $this->_set('title', $title);
    }
    
    public function getTitle() {
        return $this->_get('title');
    }
    
    public function setTargetHref($th) {
        $this->_set('target_href', $th);
    }
    
    public function getTargetHref() {
        return $this->_get('target_href');
    }
    public function setUp() {
        parent::setUp();
        $this->hasOne('Media_Model_Doctrine_Photo as PhotoRoot', array(
            'local' => 'photo_root_id',
            'foreign' => 'id'
        ));
    }
}