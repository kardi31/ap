<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Version5 extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->dropTable('news_serwis2_news');
        $this->dropTable('news_serwis2_news_translation');
        $this->dropTable('news_serwis3_news');
        $this->dropTable('news_serwis3_news_translation');
        $this->dropTable('newsletter_list');
        $this->dropTable('newsletter_users');
        $this->dropTable('news_serwis1_news_translation');
        $this->createTable('news_serwis1_assign', array(
             'news_id' => 
             array(
              'primary' => '1',
              'type' => 'integer',
              'length' => '4',
             ),
             'service_id' => 
             array(
              'primary' => '1',
              'type' => 'integer',
              'length' => '4',
             ),
             ), array(
             'type' => 'MyISAM',
             'primary' => 
             array(
              0 => 'news_id',
              1 => 'service_id',
             ),
             'collate' => 'utf8_general_ci',
             'charset' => 'utf8',
             ));
        $this->addColumn('news_serwis1_news', 'title', 'string', '255', array(
             ));
        $this->addColumn('news_serwis1_news', 'slug', 'string', '255', array(
             ));
        $this->addColumn('news_serwis1_news', 'content', 'clob', '', array(
             ));
        $this->changeColumn('news_serwis1_news', 'author_id', 'integer', '4', array(
             ));
    }

    public function down()
    {
        $this->createTable('news_serwis2_news', array(
             'id' => 
             array(
              'primary' => '1',
              'autoincrement' => '1',
              'type' => 'integer',
              'length' => '4',
             ),
             'author_id' => 
             array(
              'type' => 'integer',
              'length' => '4',
             ),
             'last_editor_id' => 
             array(
              'type' => 'integer',
              'length' => '4',
             ),
             'publish' => 
             array(
              'type' => 'boolean',
              'default' => '1',
              'length' => '25',
             ),
             'publish_date' => 
             array(
              'type' => 'timestamp',
              'length' => '25',
             ),
             'photo_root_id' => 
             array(
              'type' => 'integer',
              'length' => '4',
             ),
             'metatag_id' => 
             array(
              'type' => 'integer',
              'length' => '4',
             ),
             'created_at' => 
             array(
              'notnull' => '1',
              'type' => 'timestamp',
              'length' => '25',
             ),
             'updated_at' => 
             array(
              'notnull' => '1',
              'type' => 'timestamp',
              'length' => '25',
             ),
             'deleted_at' => 
             array(
              'notnull' => '',
              'type' => 'timestamp',
              'length' => '25',
             ),
             ), array(
             'type' => 'MyISAM',
             'indexes' => 
             array(
             ),
             'primary' => 
             array(
              0 => 'id',
             ),
             'collate' => 'utf8_general_ci',
             'charset' => 'utf8',
             ));
        $this->createTable('news_serwis2_news_translation', array(
             'id' => 
             array(
              'type' => 'integer',
              'length' => '4',
              'primary' => '1',
             ),
             'title' => 
             array(
              'type' => 'string',
              'length' => '255',
             ),
             'slug' => 
             array(
              'type' => 'string',
              'length' => '255',
             ),
             'content' => 
             array(
              'type' => 'clob',
              'length' => '',
             ),
             'lang' => 
             array(
              'fixed' => '1',
              'primary' => '1',
              'type' => 'string',
              'length' => '2',
             ),
             ), array(
             'type' => 'MyISAM',
             'indexes' => 
             array(
             ),
             'primary' => 
             array(
              0 => 'id',
              1 => 'lang',
             ),
             'collate' => 'utf8_general_ci',
             'charset' => 'utf8',
             ));
        $this->createTable('news_serwis3_news', array(
             'id' => 
             array(
              'primary' => '1',
              'autoincrement' => '1',
              'type' => 'integer',
              'length' => '4',
             ),
             'author_id' => 
             array(
              'type' => 'integer',
              'length' => '4',
             ),
             'last_editor_id' => 
             array(
              'type' => 'integer',
              'length' => '4',
             ),
             'publish' => 
             array(
              'type' => 'boolean',
              'default' => '1',
              'length' => '25',
             ),
             'publish_date' => 
             array(
              'type' => 'timestamp',
              'length' => '25',
             ),
             'photo_root_id' => 
             array(
              'type' => 'integer',
              'length' => '4',
             ),
             'metatag_id' => 
             array(
              'type' => 'integer',
              'length' => '4',
             ),
             'created_at' => 
             array(
              'notnull' => '1',
              'type' => 'timestamp',
              'length' => '25',
             ),
             'updated_at' => 
             array(
              'notnull' => '1',
              'type' => 'timestamp',
              'length' => '25',
             ),
             'deleted_at' => 
             array(
              'notnull' => '',
              'type' => 'timestamp',
              'length' => '25',
             ),
             ), array(
             'type' => 'MyISAM',
             'indexes' => 
             array(
             ),
             'primary' => 
             array(
              0 => 'id',
             ),
             'collate' => 'utf8_general_ci',
             'charset' => 'utf8',
             ));
        $this->createTable('news_serwis3_news_translation', array(
             'id' => 
             array(
              'type' => 'integer',
              'length' => '4',
              'primary' => '1',
             ),
             'title' => 
             array(
              'type' => 'string',
              'length' => '255',
             ),
             'slug' => 
             array(
              'type' => 'string',
              'length' => '255',
             ),
             'content' => 
             array(
              'type' => 'clob',
              'length' => '',
             ),
             'lang' => 
             array(
              'fixed' => '1',
              'primary' => '1',
              'type' => 'string',
              'length' => '2',
             ),
             ), array(
             'type' => 'MyISAM',
             'indexes' => 
             array(
             ),
             'primary' => 
             array(
              0 => 'id',
              1 => 'lang',
             ),
             'collate' => 'utf8_general_ci',
             'charset' => 'utf8',
             ));
        $this->createTable('newsletter_list', array(
             'id' => 
             array(
              'primary' => '1',
              'autoincrement' => '1',
              'type' => 'integer',
              'length' => '4',
             ),
             'title' => 
             array(
              'type' => 'string',
              'length' => '255',
             ),
             'slug' => 
             array(
              'type' => 'string',
              'length' => '255',
             ),
             'content' => 
             array(
              'type' => 'clob',
              'length' => '',
             ),
             'publish' => 
             array(
              'type' => 'boolean',
              'default' => '1',
              'length' => '25',
             ),
             'publish_date' => 
             array(
              'type' => 'timestamp',
              'length' => '25',
             ),
             'created_at' => 
             array(
              'notnull' => '1',
              'type' => 'timestamp',
              'length' => '25',
             ),
             'updated_at' => 
             array(
              'notnull' => '1',
              'type' => 'timestamp',
              'length' => '25',
             ),
             'deleted_at' => 
             array(
              'notnull' => '',
              'type' => 'timestamp',
              'length' => '25',
             ),
             ), array(
             'type' => 'MyISAM',
             'indexes' => 
             array(
             ),
             'primary' => 
             array(
              0 => 'id',
             ),
             'collate' => 'utf8_general_ci',
             'charset' => 'utf8',
             ));
        $this->createTable('newsletter_users', array(
             'id' => 
             array(
              'primary' => '1',
              'autoincrement' => '1',
              'type' => 'integer',
              'length' => '4',
             ),
             'email' => 
             array(
              'type' => 'string',
              'length' => '255',
             ),
             'service_id' => 
             array(
              'type' => 'integer',
              'length' => '4',
             ),
             'created_at' => 
             array(
              'notnull' => '1',
              'type' => 'timestamp',
              'length' => '25',
             ),
             'updated_at' => 
             array(
              'notnull' => '1',
              'type' => 'timestamp',
              'length' => '25',
             ),
             'deleted_at' => 
             array(
              'notnull' => '',
              'type' => 'timestamp',
              'length' => '25',
             ),
             ), array(
             'type' => 'MyISAM',
             'indexes' => 
             array(
             ),
             'primary' => 
             array(
              0 => 'id',
             ),
             'collate' => 'utf8_general_ci',
             'charset' => 'utf8',
             ));
        $this->createTable('news_serwis1_news_translation', array(
             'id' => 
             array(
              'type' => 'integer',
              'length' => '4',
              'primary' => '1',
             ),
             'title' => 
             array(
              'type' => 'string',
              'length' => '255',
             ),
             'slug' => 
             array(
              'type' => 'string',
              'length' => '255',
             ),
             'content' => 
             array(
              'type' => 'clob',
              'length' => '',
             ),
             'lang' => 
             array(
              'fixed' => '1',
              'primary' => '1',
              'type' => 'string',
              'length' => '2',
             ),
             ), array(
             'type' => 'MyISAM',
             'indexes' => 
             array(
             ),
             'primary' => 
             array(
              0 => 'id',
              1 => 'lang',
             ),
             'collate' => 'utf8_general_ci',
             'charset' => 'utf8',
             ));
        $this->dropTable('news_serwis1_assign');
        $this->removeColumn('news_serwis1_news', 'title');
        $this->removeColumn('news_serwis1_news', 'slug');
        $this->removeColumn('news_serwis1_news', 'content');
    }
}