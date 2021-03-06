<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Version10 extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->addColumn('order_consignment_type', 'deleted_at', 'timestamp', '25', array(
             'notnull' => '',
             ));
        $this->addColumn('order_payment_type', 'deleted_at', 'timestamp', '25', array(
             'notnull' => '',
             ));
    }

    public function down()
    {
        $this->removeColumn('order_consignment_type', 'deleted_at');
        $this->removeColumn('order_payment_type', 'deleted_at');
    }
}