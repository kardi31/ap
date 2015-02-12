<?php

/**
 * Product_Model_Doctrine_Producer
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    Admi
 * @subpackage Product
 * @author     Andrzej Wilczyński <and.wilczynski@gmail.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Product_Model_Doctrine_Producer extends Product_Model_Doctrine_BaseProducer
{
    public static $producerLogoPhotoDimensions = array(
        '126x126' => 'Photo in admin panel', // admin
        '267x' => 'Logo',
    );
    
    public static $producerPhotoDimensions = array(
        '126x126' => 'Photo in admin panel', // admin
        '660x440' => 'Main photo'
    );
    
    public function getId() {
        return $this->_get('id');
    }
    
    public function getWebsite() {
        return $this->_get('website');
    }
    
    public function getMetatagId() {
        return $this->_get('metatag_id');    
    }
    
    public function getDescription() {
        return $this->_get('description');
    }
    
    public function getAddress() {
        return $this->_get('address');
    }
    
    public function getPostCode() {
        return $this->_get('post_code');
    }
    
    public function getNip() {
        return $this->_get('nip');
    }
    
    public function getMail() {
        return $this->_get('email');
    }
    
    public function getCordX() {
        return $this->_get('cord_x');
    }
    
    public function getCordY() {
        return $this->_get('cord_y');
    }

    public function isStatus() {
        return $this->_get('status');
    }
    
    public function setStatus($status = true) {
        $this->_set('status', $status);
    }
    
    public function setDiscountId($discountId) {
        $this->_set('discount_id', $discountId);
    }
    
    public static function getLogoPhotoDimensions() {
        return self::$producerLogoPhotoDimensions;
    }
    
    public static function getProducerPhotoDimensions() {
        return self::$producerPhotoDimensions;
    }
    
    public function setUp() {
        $this->hasOne('Media_Model_Doctrine_Photo as PhotoRoot', array(
            'local' => 'photo_root_id',
            'foreign' => 'id'
        ));
        
        $this->hasMany('Media_Model_Doctrine_Photo as Photos', array(
            'local' => 'photo_root_id',
            'foreign' => 'root_id'
        ));
        
        $this->hasMany('Product_Model_Doctrine_Product as Products', array(
            'local' => 'id',
            'foreign' => 'producer_id'
        ));
        
        $this->hasOne('Default_Model_Doctrine_Metatag as Metatags', array(
            'local' => 'metatag_id',
            'foregin' => 'id'
        ));
        parent::setUp();
    }
}