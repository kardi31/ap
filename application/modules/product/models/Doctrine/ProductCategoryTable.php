<?php

/**
 * Product_Model_Doctrine_ProductCategoryTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Product_Model_Doctrine_ProductCategoryTable extends Doctrine_Table
{
    /**
     * Returns an instance of this class.
     *
     * @return object Product_Model_Doctrine_ProductCategoryTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Product_Model_Doctrine_ProductCategory');
    }
}