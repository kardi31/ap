<?php

/**
 * Gallery
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Tournament_DataTables_Adapter_Match extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('m');
        $q->addSelect('m.*');
        $q->addSelect('t1.*');
        $q->addSelect('t2.*');
        $q->leftJoin('m.Team1 t1');
        $q->leftJoin('m.Team2 t2');
        if($tournament_id = $this->request->getParam('tournament_id')){
            $q->addWhere('m.tournament_id = ?',$tournament_id);
        }
        return $q;
    }
}
