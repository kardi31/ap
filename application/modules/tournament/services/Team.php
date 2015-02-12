<?php

/**
 * Tournament_Service_Team
 *
@author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Tournament_Service_Team extends MF_Service_ServiceAbstract {
    
    protected $teamTable;
    
    public function init() {
        $this->teamTable = Doctrine_Core::getTable('Tournament_Model_Doctrine_Team');
        parent::init();
    }
    
    public function getTeam($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->teamTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getTournamentTeams($id, $field = 'slug', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        $q = $this->teamTable->createQuery('t');
        $q->leftJoin('t.Tournament l');
        $q->addWhere('l.'.$field." = ?",$id);
        return $q->execute(array(),$hydrationMode);
    }
    
    public function getGroups($id, $field = 'slug', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        $q = $this->teamTable->createQuery('t');
        $q->leftJoin('t.Tournament l');
        $q->addWhere('l.'.$field." = ?",$id);
        $q->groupBy('t.group_id');
        return $q->execute(array(),$hydrationMode);
    }
    
    public function getTournamentGroupTeams($id,$group_id, $field = 'slug', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        $q = $this->teamTable->createQuery('t');
        $q->leftJoin('t.Tournament l');
        $q->addWhere('l.'.$field." = ?",$id);
        $q->addWhere('t.group_id = ?',$group_id);
        return $q->execute(array(),$hydrationMode);
    }
    
    
    public function getGroupTeams($hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        
        $q = $this->teamTable->createQuery('t');
        $result = $q->execute(array(),$hydrationMode);
        $groups = array();
        foreach($result as $team):
            $groups['Grupa '.$team['group_id']][] = $team['name'];
        endforeach;
        return $groups;
    }
    
    public function getTeamPlayers($id, $field = 'slug', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        $q = $this->teamTable->createQuery('t');
        $q->leftJoin('t.Players p');
        $q->addWhere('t.'.$field." = ?",$id);
        $wynik = $q->fetchOne(array(),$hydrationMode);
        return $wynik['Players'];
    }
    
    public function getTeamsTimetable($tournament_id,$hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        $q = $this->teamTable->createQuery('t');
        $q->addWhere('t.tournament_id = ?',$tournament_id);
        $q->select('t.*');
        $q->leftJoin('t.Matches1 m1');
        $q->leftJoin('t.Matches2 m2');
	$q->addWhere('m1.played = 1');
	$q->addWhere('m2.played = 1');
        $q->addSelect('m1.*');
        $q->addSelect('m2.*');
        $res = $q->execute(array(),$hydrationMode);
        foreach($res as $key=>$r):
            foreach($r['Matches1'] as $match):
                if($r['id']==$match->team1)
                    $result[$r->slug][] = $match['Team2']['slug'];
                else
                    $result[$r->slug][] = $match['Team1']['slug'];
            endforeach;
            
            foreach($r['Matches2'] as $match):
                if($r['id']==$match->team1)
                    $result[$r->slug][] = $match['Team2']['slug'];
                else
                    $result[$r->slug][] = $match['Team1']['slug'];
            endforeach;
            $t[$r->slug] = array_count_values($result[$r->slug]);
          
        endforeach;
        return $t;
        
    }
    public function getGroupTeamForm(Tournament_Service_Team $team = null) {
        $form = new Tournament_Form_GroupTeam();
        if(null != $team) { 
            $form->populate($team->toArray());
        }
        return $form;
    }
    
    public function saveTeamFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        if(!$order = $this->getTeam((int) $values['id'])) {
            $order = $this->teamTable->getRecord();
        }
        $order->fromArray($values);
        $order->slug = MF_Text::createUniqueTableSlug('Tournament_Model_Doctrine_Team',$values['name'],$order->get('id'));
        $order->save();
        
        return $order;
    }
       
    public function getFullOrder($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->teamTable->getFullOrderQuery();
        $q->andWhere('o.' . $field . ' = ?', $id);
        return $q->fetchOne(array(), $hydrationMode);
    }
    
    public function getUserOrders($email, $field = 'email', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->teamTable->getFullOrderQuery();
        $q->andWhere('u.' . $field . ' like ?', $email);
        $q->addOrderBy('o.created_at');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getNewOrders($date, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->teamTable->getFullOrderQuery();
        $q->andWhere('o.created_at > ?', $date);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getAllOrders($countOnly = false) {
        if(true == $countOnly) {
            return $this->teamTable->count();
        } else {
            return $this->teamTable->findAll();
        }
    }
    
    public function getCart() {
        if(!$this->cart) {
            $this->cart = new Order_Model_Cart();
        }
        return $this->cart;
    }
    
}
?>