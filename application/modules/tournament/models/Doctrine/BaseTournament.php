<?php

/**
 * Tournament_Model_Doctrine_BaseTournament
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $name
 * @property string $slug
 * @property boolean $active
 * @property timestamp $date_from
 * @property timestamp $date_to
 * @property Doctrine_Collection $Team
 * @property Tournament_Model_Doctrine_Match $Matches
 * 
 * @package    Admi
 * @subpackage Tournament
 * @author     Michał Folga <michalfolga@gmail.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Tournament_Model_Doctrine_BaseTournament extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('tournament_tournament');
        $this->hasColumn('id', 'integer', 4, array(
             'primary' => true,
             'autoincrement' => true,
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('name', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('slug', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('active', 'boolean', null, array(
             'type' => 'boolean',
             'default' => 1,
             ));
        $this->hasColumn('date_from', 'timestamp', null, array(
             'type' => 'timestamp',
             ));
        $this->hasColumn('date_to', 'timestamp', null, array(
             'type' => 'timestamp',
             ));

        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_general_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasMany('Tournament_Model_Doctrine_Team as Team', array(
             'local' => 'id',
             'foreign' => 'tournament_id'));

        $this->hasOne('Tournament_Model_Doctrine_Match as Matches', array(
             'local' => 'id',
             'foreign' => 'tournament_id'));
    }
}