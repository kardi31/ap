<?php

/**
 * Menu_Model_Doctrine_BaseMenuItemTranslation
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $lang
 * @property string $target_href
 * @property string $title
 * @property string $title_attr
 * @property string $slug
 * @property Menu_Model_Doctrine_MenuItem $MenuItem
 * 
 * @package    Admi
 * @subpackage Menu
 * @author     Michał Folga <michalfolga@gmail.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Menu_Model_Doctrine_BaseMenuItemTranslation extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('menu_menu_item_translation');
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
        $this->hasColumn('target_href', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('title', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('title_attr', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('slug', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));

        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_general_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Menu_Model_Doctrine_MenuItem as MenuItem', array(
             'local' => 'id',
             'foreign' => 'id'));
    }
}