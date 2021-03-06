<?php

/**
 * Affilateprogram_Model_Doctrine_BasePartnerOrders
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $partner_id
 * @property integer $order_id
 * @property Affilateprogram_Model_Doctrine_Partner $Partner
 * 
 * @package    Admi
 * @subpackage Affilateprogram
 * @author     Andrzej Wilczyński <and.wilczynski@gmail.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Affilateprogram_Model_Doctrine_BasePartnerOrders extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('affilateprogram_partner_orders');
        $this->hasColumn('partner_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('order_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));

        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_general_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Affilateprogram_Model_Doctrine_Partner as Partner', array(
             'local' => 'partner_id',
             'foreign' => 'id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}