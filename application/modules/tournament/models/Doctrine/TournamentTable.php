<?php

/**
 * Tournament_Model_Doctrine_TournamentTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Tournament_Model_Doctrine_TournamentTable extends Doctrine_Table
{
    /**
     * Returns an instance of this class.
     *
     * @return object Tournament_Model_Doctrine_TournamentTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Tournament_Model_Doctrine_Tournament');
    }
}