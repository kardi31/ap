<?php

/**
 * Default_Model_Doctrine_Setting
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    Admi
 * @subpackage Default
 * @author     Michał Folga <michalfolga@gmail.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Default_Model_Doctrine_Setting extends Default_Model_Doctrine_BaseSetting {
    
    public static $availableSettings = array(
        'contact_email' => 'Contact email',
        'ga_profile_id' => 'Google Analytics Profile Id',
        'company_name' => 'Company name',
        'company_address' => 'Company address',
        'company_city' => 'Company city',
        'company_province' => 'Company province',
        'company_postal_code' => 'Company postal code',
        'company_phone' => 'Company phone',
        'company_fax' => 'Company fax'
    );
    
    public static function getAvailableSettings() {
        return self::$availableSettings;
    }
    
    public function setId($id) {
        $this->_set('id', $id);
    }
    
    public function getId() {
        return $this->_get('id');
    }
    
    public function setValue($value) {
        $this->_set('value', $value);
    }
    
    public function getValue() {
        return $this->_get('value');
    }
    
    public function setLabel($label) {
        $this->_set('label', $label);
    }
    
    public function getLabel() {
        return $this->_get('label');
    }
}