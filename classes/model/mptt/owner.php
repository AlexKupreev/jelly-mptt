<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * 
 * Jelly owner test model  
 *
 * @package MPTT
 * @author Alexander Kupreyeu (Kupreev) (alexander dot kupreev at gmail dot com, http://kupreev.com)
 */
class Model_MPTT_Owner extends Jelly_Model {
    
    protected $table = 'jelly_mptt_owner';
    
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
            ->table('jelly_mptt_owner')
            ->fields = array(
            'id' => new Field_Primary,
            'owned' => new Field_HasMany(array(
                'foreign' => 'owned.owner_id',
                )),
                );
    }
    
    public function create_tables()
    {    
        //$this->delete_table(); 
        $this->_db->query(NULL, 'CREATE TABLE IF NOT EXISTS `'.$this->_db->table_prefix().$this->table.'` (`id` INT( 255 ) UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY ( `id` )) ENGINE = InnoDB ', TRUE);
        $this->_db->query(NULL, 'CREATE TABLE IF NOT EXISTS `'.$this->_db->table_prefix().'jelly_mptt_owned` (`id` INT( 255 ) UNSIGNED NOT NULL AUTO_INCREMENT ,`lvl` INT( 255 ) NOT NULL ,`lft` INT( 255 ) NOT NULL ,`rgt` INT( 255 ) NOT NULL ,`scope` INT( 255 ) NOT NULL ,`owner_id` INT( 255 ) UNSIGNED NOT NULL , `name` VARCHAR( 255 ) NOT NULL ,PRIMARY KEY ( `id` ), FOREIGN KEY fk_own (owner_id) REFERENCES '.$this->_db->table_prefix().$this->table.' (id) ON DELETE NO ACTION ON UPDATE NO ACTION ) ENGINE = InnoDB ', TRUE);
        
        $this->reset_tables();
    }
    
    public function reset_tables()
    {
        $this->_db->query(NULL, 'TRUNCATE TABLE `'.$this->_db->table_prefix().'jelly_mptt_owned`', TRUE);
        $this->_db->query(NULL, 'TRUNCATE TABLE `'.$this->_db->table_prefix().$this->table.'`', TRUE);
        DB::insert($this->table)->values(array('id' => 1))->execute($this->_db);
        DB::insert($this->table)->values(array('id' => 2))->execute($this->_db);
        
        DB::insert('jelly_mptt_owned')->values(array('id' => 1,'lvl' => 0,'lft' => 1, 'rgt' => 4, 'scope' => 1, 'owner_id' => 1, 'name' => 'Root Node'))->execute($this->_db);
        DB::insert('jelly_mptt_owned')->values(array('id' => 2,'lvl' => 1,'lft' => 2, 'rgt' => 3, 'scope' => 1, 'owner_id' => 1, 'name' => 'Leaf Node'))->execute($this->_db);
    }
    
    public function delete_tables()
    {   
        $this->_db->query(NULL, 'DROP TABLE IF EXISTS `'.$this->_db->table_prefix().'jelly_mptt_owned`', TRUE);
        $this->_db->query(NULL, 'DROP TABLE IF EXISTS `'.$this->_db->table_prefix().$this->table.'`', TRUE);
    }
}