<?php

/**
 * Order_Model_Doctrine_BasePaymentType
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $name
 * @property clob $description
 * @property Doctrine_Collection $Payments
 * 
 * @package    Admi
 * @subpackage Order
 * @author     Michał Folga <michalfolga@gmail.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Order_Model_Doctrine_BasePaymentType extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('order_payment_type');
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
        $this->hasMany('Order_Model_Doctrine_Payment as Payments', array(
             'local' => 'id',
             'foreign' => 'payment_type_id'));

        $softdelete0 = new Doctrine_Template_SoftDelete();
        $this->actAs($softdelete0);
    }
}