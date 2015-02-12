<?php

/**
 * League_Model_Doctrine_BaseMatch
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $team1
 * @property integer $team2
 * @property integer $goal1
 * @property integer $goal2
 * @property integer $league_id
 * @property datetime $match_date
 * @property boolean $played
 * @property League_Model_Doctrine_Team $Team1
 * @property League_Model_Doctrine_League $League
 * @property League_Model_Doctrine_Shooter $Shooters
 * 
 * @package    Admi
 * @subpackage League
 * @author     Michał Folga <michalfolga@gmail.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class League_Model_Doctrine_BaseMatch extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('league_match');
        $this->hasColumn('id', 'integer', 4, array(
             'primary' => true,
             'autoincrement' => true,
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('team1', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('team2', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('goal1', 'integer', 11, array(
             'type' => 'integer',
             'length' => '11',
             ));
        $this->hasColumn('goal2', 'integer', 11, array(
             'type' => 'integer',
             'length' => '11',
             ));
        $this->hasColumn('league_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('match_date', 'datetime', null, array(
             'type' => 'datetime',
             ));
        $this->hasColumn('played', 'boolean', null, array(
             'type' => 'boolean',
             'default' => 0,
             ));

        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_general_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('League_Model_Doctrine_Team as Team1', array(
             'local' => 'team1',
             'foreign' => 'id'));

        $this->hasOne('League_Model_Doctrine_League as League', array(
             'local' => 'league_id',
             'foreign' => 'id'));

        $this->hasOne('League_Model_Doctrine_Shooter as Shooters', array(
             'local' => 'id',
             'foreign' => 'match_id'));
    }
}