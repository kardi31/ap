<?php

/**
 * Testimonial_Model_Doctrine_BaseTestimonialTranslation
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $lang
 * @property clob $description
 * @property Testimonial_Model_Doctrine_Testimonial $Testimonial
 * 
 * @package    Admi
 * @subpackage Testimonial
 * @author     Andrzej Wilczyński <and.wilczynski@gmail.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Testimonial_Model_Doctrine_BaseTestimonialTranslation extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('testimonial_testimonial_translation');
        $this->hasColumn('id', 'integer', 4, array(
             'primary' => true,
             'autoincrement' => true,
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('lang', 'string', 64, array(
             'primary' => true,
             'type' => 'string',
             'length' => '64',
             ));
        $this->hasColumn('description', 'clob', null, array(
             'type' => 'clob',
             ));

        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_general_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Testimonial_Model_Doctrine_Testimonial as Testimonial', array(
             'local' => 'id',
             'foreign' => 'id'));
    }
}