<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Version1 extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->removeColumn('product_attachment', 'type');
        $this->addColumn('product_attachment', 'extension', 'string', '255', array(
             ));
    }

    public function down()
    {
        $this->addColumn('product_attachment', 'type', 'string', '255', array(
             ));
        $this->removeColumn('product_attachment', 'extension');
    }
}