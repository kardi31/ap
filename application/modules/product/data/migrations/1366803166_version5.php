<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Version5 extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->addColumn('product_product', 'youtube', 'string', '255', array(
             ));
    }

    public function down()
    {
        $this->removeColumn('product_product', 'youtube');
    }
}