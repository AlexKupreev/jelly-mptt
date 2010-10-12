<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * 
 * Jelly_MPTT test model ported from Sprig_MPTT test model  
 *
 * @package MPTT
 * @author Mathew Davies
 * @author Kiall Mac Innes
 * @author Paul Banks
 * @author Alexander Kupreyeu (Kupreev) (alexander dot kupreev at gmail dot com, http://kupreev.com)
 */
class Model_MPTT_Test extends Jelly_Model_MPTT {
    
    protected $table = 'jelly_mptt_test';
    
    protected $_db;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->_db = Database::instance('unit_testing');
    }
    
    public static function initialize(Jelly_Meta $meta)
    {
        // Notice how the MPTT fields are added automatically
        $meta->db('unit_testing')
            ->table('jelly_mptt_test')
            ->fields = array(
            'id' => new Field_Primary,
            'name' => new Field_String,
            );
        
        parent::initialize($meta);
    }
    
    public function create_table()
    {
        $this->delete_table();
        $this->_db->query(NULL, 'CREATE TABLE `'.$this->_db->table_prefix().$this->table.'` (`id` INT( 255 ) UNSIGNED NOT NULL AUTO_INCREMENT ,`lvl` INT( 255 ) NOT NULL ,`lft` INT( 255 ) NOT NULL ,`rgt` INT( 255 ) NOT NULL ,`scope` INT( 255 ) NOT NULL ,`name` VARCHAR( 255 ) NOT NULL ,PRIMARY KEY ( `id` )) ENGINE = MYISAM ', TRUE);
        $this->reset_table();
    }
    
    public function reset_table()
    {
        $this->_db->query(NULL, 'TRUNCATE TABLE `'.$this->_db->table_prefix().$this->table.'`', TRUE);
        DB::insert($this->table)->values(array('id' => 1,'lvl' => 0,'lft' => 1, 'rgt' => 22, 'scope' => 1, 'name' => 'Root Node'))->execute($this->_db);

        DB::insert($this->table)->values(array('id' => 2,'lvl' => 1,'lft' => 2, 'rgt' => 3, 'scope' => 1, 'name' => 'Leaf Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 3,'lvl' => 1,'lft' => 4, 'rgt' => 7, 'scope' => 1, 'name' => 'Normal Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 4,'lvl' => 2,'lft' => 5, 'rgt' => 6, 'scope' => 1, 'name' => 'Leaf Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 5,'lvl' => 1,'lft' => 8, 'rgt' => 9, 'scope' => 1, 'name' => 'Leaf Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 6,'lvl' => 1,'lft' => 10, 'rgt' => 21, 'scope' => 1, 'name' => 'Normal Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 7,'lvl' => 2,'lft' => 11, 'rgt' => 12, 'scope' => 1, 'name' => 'Leaf Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 8,'lvl' => 2,'lft' => 13, 'rgt' => 18, 'scope' => 1, 'name' => 'Normal Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 9,'lvl' => 3,'lft' => 14, 'rgt' => 15, 'scope' => 1, 'name' => 'Leaf Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 10,'lvl' => 3,'lft' => 16, 'rgt' => 17, 'scope' => 1, 'name' => 'Leaf Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 11,'lvl' => 2,'lft' => 19, 'rgt' => 20, 'scope' => 1, 'name' => 'Leaf Node'))->execute($this->_db);
        
        DB::insert($this->table)->values(array('id' => 12,'lvl' => 0,'lft' => 1, 'rgt' => 22, 'scope' => 2, 'name' => 'Root Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 13,'lvl' => 1,'lft' => 2, 'rgt' => 3, 'scope' => 2, 'name' => 'Leaf Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 14,'lvl' => 1,'lft' => 4, 'rgt' => 7, 'scope' => 2, 'name' => 'Normal Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 15,'lvl' => 2,'lft' => 5, 'rgt' => 6, 'scope' => 2, 'name' => 'Leaf Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 16,'lvl' => 1,'lft' => 8, 'rgt' => 9, 'scope' => 2, 'name' => 'Leaf Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 17,'lvl' => 1,'lft' => 10, 'rgt' => 21, 'scope' => 2, 'name' => 'Normal Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 18,'lvl' => 2,'lft' => 11, 'rgt' => 12, 'scope' => 2, 'name' => 'Leaf Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 19,'lvl' => 2,'lft' => 13, 'rgt' => 18, 'scope' => 2, 'name' => 'Normal Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 20,'lvl' => 3,'lft' => 14, 'rgt' => 15, 'scope' => 2, 'name' => 'Leaf Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 21,'lvl' => 3,'lft' => 16, 'rgt' => 17, 'scope' => 2, 'name' => 'Leaf Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 22,'lvl' => 2,'lft' => 19, 'rgt' => 20, 'scope' => 2, 'name' => 'Leaf Node'))->execute($this->_db);
        
        DB::insert($this->table)->values(array('id' => 23,'lvl' => 0,'lft' => 1, 'rgt' => 22, 'scope' => 3, 'name' => 'Root Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 24,'lvl' => 1,'lft' => 2, 'rgt' => 3, 'scope' => 3, 'name' => 'Leaf Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 25,'lvl' => 1,'lft' => 4, 'rgt' => 7, 'scope' => 3, 'name' => 'Normal Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 26,'lvl' => 2,'lft' => 5, 'rgt' => 6, 'scope' => 3, 'name' => 'Leaf Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 27,'lvl' => 1,'lft' => 8, 'rgt' => 9, 'scope' => 3, 'name' => 'Leaf Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 28,'lvl' => 1,'lft' => 10, 'rgt' => 21, 'scope' => 3, 'name' => 'Normal Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 29,'lvl' => 2,'lft' => 11, 'rgt' => 12, 'scope' => 3, 'name' => 'Leaf Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 30,'lvl' => 2,'lft' => 13, 'rgt' => 18, 'scope' => 3, 'name' => 'Normal Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 31,'lvl' => 3,'lft' => 14, 'rgt' => 15, 'scope' => 3, 'name' => 'Leaf Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 32,'lvl' => 3,'lft' => 16, 'rgt' => 17, 'scope' => 3, 'name' => 'Leaf Node'))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 33,'lvl' => 2,'lft' => 19, 'rgt' => 20, 'scope' => 3, 'name' => 'Leaf Node'))->execute($this->_db);
    }
    
    public function delete_table()
    {
        $this->_db->query(NULL, 'DROP TABLE IF EXISTS `'.$this->_db->table_prefix().$this->table.'`', TRUE);
    }
}