<?php

/**
 * Menu_Model_Doctrine_Menu
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    Admi
 * @subpackage Menu
 * @author     Michał Folga <michalfolga@gmail.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Menu_Model_Doctrine_Menu extends Menu_Model_Doctrine_BaseMenu
{
    public function setId($id) {
        $this->_set('id', $id);
    }
    
    public function getId() {
        return $this->_get('id');
    }
    
    public function setName($name) {
        $this->_set('name', $name);
    }
    
    public function getName() {
        return $this->_get('name');
    }

    public function setLocation($location) {
        $this->_set('location', $location);
    }
    
    public function getLocation() {
        return $this->_get('location');
    }
}