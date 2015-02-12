<?php

/**
 * Tournament_Service_Tournament
 *
@author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Tournament_Service_Tournament extends MF_Service_ServiceAbstract {
    
    protected $tournamentTable;
    
    public function init() {
        $this->tournamentTable = Doctrine_Core::getTable('Tournament_Model_Doctrine_Tournament');
        parent::init();
    }
    
    public function getTournament($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->tournamentTable->findOneBy($field, $id, $hydrationMode);
    }
    
    
    
    public function getTournamentForm(Tournament_Model_Doctrine_Tournament $orderStatus = null) {
        $form = new Tournament_Form_Tournament();
        if(null != $orderStatus) { 
            $form->populate($orderStatus->toArray());
        }
        return $form;
    }
    
    public function saveTournamentFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$orderStatus = $this->getTournament((int) $values['id'])) {
            $orderStatus = $this->tournamentTable->getRecord();
        }
         
        $orderStatus->fromArray($values);
        $orderStatus->save();
        
        return $orderStatus;
    }
    
    public function removeTournament(Tournament_Model_Doctrine_Tournament $orderStatus) {
        $orderStatus->delete();
    }
    
    public function getAllTournament($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->tournamentTable->getTournamentQuery();
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getActiveTournaments($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->tournamentTable->createQuery('l');
        $q->addWhere('l.active = 1');
        $q->orderBy('l.id DESC');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getTargetTournamentSelectOptions($prependEmptyValue = false) {
        $items = $this->getAllTournament();
        $result = array();
        if($prependEmptyValue) {
            $result[''] = ' ';
        }
        foreach($items as $item) {
                $result[$item->getId()] = $item->name;
        }
        return $result;
    }
}
?>