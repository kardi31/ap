<?php

/**
 * Gallery
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Tournament_DataTables_Adapter_Booking extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('b');
        $q->addSelect('b.*');
        $q->addSelect('p.*');
        $q->addSelect('t.*');
        $q->leftJoin('b.Player p');
        $q->leftJoin('p.Team t');
        if($tournament_id = $this->request->getParam('tournament_id')){
            $q->addWhere('t.tournament_id = ?',$tournament_id);
        }
        return $q;
    }
}
