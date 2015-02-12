<?php

/**
 * Attachment_Model_Doctrine_Attachment
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    Admi
 * @subpackage Attachment
 * @author     Andrzej Wilczyński <and.wilczynski@gmail.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Attachment_Model_Doctrine_Attachment extends Attachment_Model_Doctrine_BaseAttachment
{
    public static $attachmentPhotoDimensions = array(
        '126x126' => 'Photo in admin panel',                  // admin
        '400x200' => 'Main photo'
    );
    
    public static function getAttachmentPhotoDimensions() {
        return self::$attachmentPhotoDimensions;
    }
    
    public function setId($id) {
        $this->_set('id', $id);
    }
    
    public function getId() {
        return $this->_get('id');
    }
    
    public function setFilename($filename) {
        $this->_set('filename', $filename);
    }
    
    public function getFilename() {
        return $this->_get('filename');
    }
    
    public function setTitle($title) {
        $this->_set('title', $title);
    }
    
    public function getTitle(){
        return $this->_get('title');
    }
    
    public function setExtension($extension) {
        $this->_set('extension', $extension);
    }
    
    public function setUp() {
        $this->hasOne('Media_Model_Doctrine_Photo as PhotoRoot', array(
            'local' => 'photo_root_id',
            'foreign' => 'id'
        ));
        
        parent::setUp();
    }
}