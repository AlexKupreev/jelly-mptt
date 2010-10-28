<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * 
 * Jelly_MPTT owned test model   
 *
 * @package MPTT
 * @author Alexander Kupreyeu (Kupreev) (alexander dot kupreev at gmail dot com, http://kupreev.com)
 */
class Model_MPTT_Owned extends Jelly_Model_MPTT {
    
    protected $table = 'jelly_mptt_owned';
    
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
            ->table('jelly_mptt_owned')
            ->fields = array(
            'id' => new Field_Primary,
            'owner' => new Field_BelongsTo,
            'name' => new Field_String,
            );
        
        parent::initialize($meta);
    }
    
}