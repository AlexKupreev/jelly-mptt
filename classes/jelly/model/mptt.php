<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Modified Preorder Tree Traversal Class.
 * 
 * Ported for Jelly from Paul Banks' Sprig_MPTT that in turn has been ported 
 * from ORM_MPTT originally created by Matthew Davies and Kiall Mac Innes
 *
 * @package Jelly_MPTT
 * @author Mathew Davies
 * @author Kiall Mac Innes
 * @author Paul Banks
 * @author Alexander Kupreyeu (Kupreev) (alexander dot kupreev at gmail dot com, http://kupreev.com)
 */
abstract class Jelly_Model_MPTT extends Jelly_Model
{
		
	/**
	 * @access protected
	 * @var string mptt view folder.
	 */
	protected $_directory = 'mptt';
	
	/**
	 * @access protected
	 * @var string default view folder.
	 */
	protected $_style = 'default';
    
    protected $_left_column = NULL;
    protected $_right_column = NULL;
    protected $_level_column = NULL;
    protected $_scope_column = NULL;
	
	/**
	 * Initialize the fields and add MPTT field defaults if not specified
	 * @param  array  $values
     * @return void
	 */
	public function __construct($values = array())
	{
		// Initialize jelly model 
		parent::__construct($values);
        
        // Check we have default values for all (MPTT) fields (otherwise we cause errors)
        foreach ($this->meta()->fields() as $name => $field)
        {
            if ($field instanceof Jelly_Field_MPTT AND ! isset($this->_original[$name]))
            {
                $this->_original[$name] = NULL;
            }
        }
        
        $this->_left_column = $this->meta()->fields('left')->column;
        $this->_right_column = $this->meta()->fields('right')->column;
        $this->_level_column = $this->meta()->fields('level')->column;
        $this->_scope_column = $this->meta()->fields('scope')->column;
    }
    
    public static function initialize(Jelly_Meta $meta)
    {
        
        $meta->fields(array(
            //'id' => new Field_Primary,
            
            'left'   => new Jelly_Field_MPTT_Left(array(
                'column' => 'lft',
                )),
            'right'  => new Jelly_Field_MPTT_Right(array(
                'column' => 'rgt',
                )),
            'level'  => new Jelly_Field_MPTT_Level(array(
                'column' => 'lvl',
                )),
            'scope'  => new Jelly_Field_MPTT_Scope(array(
                'column' => 'scope',
                )),
            ));
        
        // Check we don't have a composite primary Key
        if (is_array($meta->primary_key())) 
        {
            throw new Kohana_Exception('Jelly_MPTT does not support composite primary keys');
        }
        
    }
	

	/**
	 * Locks table.
	 *
	 * @access private
	 */
	protected function lock()
	{
		Database::instance($this->db)->query(NULL, 'LOCK TABLE '.Database::instance($this->db)->table_prefix().$this->table.' WRITE', TRUE);
	}
	
	/**
	 * Unlock table.
	 *
	 * @access private
	 */
	protected function unlock()
	{
		Database::instance($this->db)->query(NULL, 'UNLOCK TABLES', TRUE);
	}

	/**
	 * Does the current node have children?
	 *
	 * @access public
	 * @return bool
	 */
	public function has_children()
	{
		return (($this->right - $this->left) > 1);
	}
	
	/**
	 * Is the current node a leaf node?
	 *
	 * @access public
	 * @return bool
	 */
	public function is_leaf()
	{
		return ! $this->has_children();
	}
	
	/**
	 * Is the current node a descendant of the supplied node.
	 *
	 * @access public
	 * @param Jelly_Model_MPTT $target Target 
	 * @return bool
	 */
	public function is_descendant($target)
	{
		return (
		    $this->left > $target->left 
				AND $this->right < $target->right 
				AND $this->scope == $target->scope
		    );
	}
	
	/**
	 * Is the current node a direct child of the supplied node?
	 *
	 * @access public
	 * @param Jelly_Model_MPTT $target Target
	 * @return bool
	 */
	public function is_child($target)
	{
		return ($this->parent->{$this->meta()->primary_key()} === $target->{$this->meta()->primary_key()});
	}
	
	/**
	 * Is the current node the direct parent of the supplied node?
	 *
	 * @access public
	 * @param Jelly_Model_MPTT $target Target
	 * @return bool
	 */
	public function is_parent($target)
	{
		return ($this->{$this->meta()->primary_key()} === $target->parent->{$this->meta()->primary_key()});
	}
	
	/**
	 * Is the current node a sibling of the supplied node
	 *
	 * @access public
	 * @param Jelly_Model_MPTT $target Target
	 * @return bool
	 */
	public function is_sibling($target)
	{
		if ($this->{$this->meta()->primary_key()} === $target->{$this->meta()->primary_key()})
			return FALSE;
		
		return ($this->parent->{$this->meta()->primary_key()} === $target->parent->{$this->meta()->primary_key()});
	}
	
	/**
	 * Is the current node a root node?
	 *
	 * @access public
	 * @return bool
	 */
	public function is_root()
	{
		return ($this->left === 1);
	}
	
	/**
	 * Returns the root node.
	 *
	 * @access public
	 * @return Jelly_Model_MPTT/FALSE on invalid scope
	 */
	public function root($scope = NULL)
	{
		if ($scope === NULL AND $this->loaded())
		{
			$scope = $this->scope;
		}
		elseif ($scope === NULL AND ! $this->loaded())
		{
			return FALSE;
		}
		
        return Jelly::select($this)
            ->where($this->_left_column, '=', 1)
            ->where($this->_scope_column, '=', $scope)
            ->limit(1)
            ->execute();
	}
	
	/**
	 * Returns the parent of the current node.
	 *
	 * @access public
	 * @return Jelly_Model_MPTT
	 */
	public function parent()
	{
		return $this->parents(TRUE, 'ASC', TRUE);
	}
	
	/**
	 * Returns the parents of the current node.
	 *
	 * @access public
	 * @param bool $root include the root node?
	 * @param string $direction direction to order the left column by.
     * @param bool $direct_parent_only
	 * @return Jelly_Model_MPTT
	 */
	public function parents($root = TRUE, $direction = 'ASC', $direct_parent_only = FALSE)
	{
		$query = Jelly::select($this)
			->where($this->_left_column, '<=', $this->left)
			->where($this->_right_column, '>=', $this->right)
			->where($this->meta()->primary_key(), '<>', $this->{$this->meta()->primary_key()})
			->where($this->_scope_column, '=', $this->scope)
			->order_by($this->_left_column, $direction);
			
		if ( ! $root)
		{
			$query->where($this->_left_column, '!=', 1);
		}	
		
		if ($direct_parent_only)
		{
			$query
                ->where($this->_level_column, '=', $this->level - 1)
			    ->limit(1);
		}
		
        $parents = $query->execute();
        		
		return $parents;
	}
	
	/**
	 * Returns the children of the current node.
	 *
	 * @access public
	 * @param bool $self include the current loaded node?
	 * @param string $direction direction to order the left column by.
     * @param int|bool $limit
	 * @return Jelly_Model_MPTT
	 */
	public function children($self = FALSE, $direction = 'ASC', $limit = FALSE)
	{
		return $this->descendants($self, $direction, TRUE, FALSE, $limit);
	}
	
	/**
	 * Returns the descendants of the current node.
	 *
	 * @access public
	 * @param bool $self include the current loaded node?
	 * @param string $direction direction to order the left column by.
     * @param bool  $direct_children_only
     * @param bool  $leaves_only
     * @param int|bool $limit
	 * @return Jelly_Model_MPTT
	 */
	public function descendants($self = FALSE, $direction = 'ASC', $direct_children_only = FALSE, $leaves_only = FALSE, $limit = FALSE)
	{		
		$left_operator = $self ? '>=' : '>';
		$right_operator = $self ? '<=' : '<';
		
		$query = Jelly::select($this)
			->where($this->_left_column, $left_operator, $this->left)
			->where($this->_right_column, $right_operator, $this->right)
			->where($this->_scope_column, '=', $this->scope)
			->order_by($this->_left_column, $direction);
		
		if ($direct_children_only)
		{
			if ($self)
			{
				$query
					->and_where_open()
					->where($this->_level_column, '=', $this->level)
					->or_where($this->_level_column, '=', $this->level + 1)
					->and_where_close();
			}
			else
			{
				$query->where($this->_level_column, '=', $this->level + 1);
			}
		}
		
		if ($leaves_only)
		{
			$query->where($this->_right_column, '=', new Database_Expression('`'.$this->_left_column.'` + 1'));
		}
        
        if ($limit)
        {
            $query->limit($limit);
        }
		
        return $query->execute();
	}
	
	/**
	 * Returns the siblings of the current node
	 *
	 * @access public
	 * @param bool $self include the current loaded node?
	 * @param string $direction direction to order the left column by.
	 * @return Jelly_Model_MPTT
	 */
	public function siblings($self = FALSE, $direction = 'ASC')
	{	
		$query = Jelly::select($this)
			->where($this->_left_column, '>', $this->parent->left)
			->where($this->_right_column, '<', $this->parent->right)
			->where($this->_scope_column, '=', $this->scope)
			->where($this->_level_column, '=', $this->level)
			->order_by($this->_left_column, $direction);
		
		if ( ! $self)
		{
			$query->where($this->meta()->primary_key(), '<>', $this->{$this->meta()->primary_key()});
		}
		
        return $query->execute();
	}
	
	/**
	 * Returns leaves under the current node.
	 *
	 * @access public
     * @param bool $self include the current loaded node?
     * @param string $direction direction to order the left column by.
	 * @return Jelly_Model_MPTT
	 */
	public function leaves($self = FALSE, $direction = 'ASC')
	{
		return $this->descendants($self, $direction, TRUE, TRUE);
	}
	
	/**
	 * Get Size
	 *
	 * @access protected
	 * @return integer
	 */
	protected function get_size()
	{
		return ($this->right - $this->left) + 1;
	}

	/**
	 * Create a gap in the tree to make room for a new node
	 *
	 * @access private
	 * @param integer $start start position.
	 * @param integer $size the size of the gap (default is 2).
     * @return void
	 */
	private function create_space($start, $size = 2)
	{
		// Update the left values, then the right.
		DB::update($this->table)
			->set(array(
                $this->_left_column => new Database_Expression('`'.$this->_left_column.'` + '.$size)
                ))
			->where($this->_left_column, '>=', $start)
			->where($this->_scope_column, '=', $this->scope)
			->execute($this->db);
			
		DB::update($this->table)
			->set(array(
                $this->_right_column => new Database_Expression('`'.$this->_right_column.'` + '.$size)
                ))
			->where($this->_right_column, '>=', $start)
			->where($this->_scope_column, '=', $this->scope)
			->execute($this->db);
	}
	
	/**
	 * Closes a gap in a tree. Mainly used after a node has
	 * been removed.
	 *
	 * @access private
	 * @param integer $start start position.
	 * @param integer $size the size of the gap (default is 2).
     * @return void
	 */
	private function delete_space($start, $size = 2)
	{
		// Update the left values, then the right.
		DB::update($this->table)
			->set(array(
                $this->_left_column => new Database_Expression('`'.$this->_left_column.'` - '.$size)
                ))
			->where($this->_left_column, '>=', $start)
			->where($this->_scope_column, '=', $this->scope)
			->execute($this->db);
			
		DB::update($this->table)
			->set(array(
                $this->_right_column => new Database_Expression('`'.$this->_right_column.'` - '.$size)
                ))
			->where($this->_right_column, '>=', $start)
			->where($this->_scope_column, '=', $this->scope)
			->execute($this->db);
	}
	
	/**
	 * Insert this object as the root of a new scope
	 * 
	 * Other object fields must be set in the normal Jelly way 
	 * otherwise validation exception will be thrown
	 *
	 * @param integer $scope New scope to create.
	 * @return Jelly_Model_MPTT
	 * @throws Validation_Exception on invalid $additional_fields data
	 **/
	public function insert_as_new_root($scope = 1)
	{	
		// Make sure the specified scope doesn't already exist.
		$root = $this->root($scope);
                
        if ($root->loaded())
			return FALSE;
		
		// Create a new root node in the new scope.
        $this->set(array(
            'left' => 1,
            'right' => 2,
            'level' => 0,
            'scope' => $scope 
            ));
		
		try
		{
			//parent::create();
            $this->save();
		}
		catch (Validate_Exception $e)
		{
			// There was an error validating the additional fields, re-thow it
			throw $e;
		}
		
		return $this;
	}
	
	/**
	 * Insert the object
	 * 
     * @access protected  
	 * @param Jelly_MPTT|mixed $target target node primary key value or Jelly_MPTT object. 
	 * @param string $copy_left_from target object property to take new left value from
	 * @param integer $left_offset offset for left value
	 * @param integer $level_offset offset for level value
     * @return Jelly_Model_MPTT
	 * @throws Validation_Exception
	 */
	protected function insert($target, $copy_left_from, $left_offset, $level_offset)
	{
		// Insert should only work on new nodes.. if its already it the tree it needs to be moved!
		if ($this->loaded())
			return FALSE;
		
		if ( ! $target instanceof $this)
		{
			$target = Jelly::select($this)
                ->load($target);
			            
			if ( ! $target->loaded())
			{
				return FALSE;
			}
		}
		else
		{
			$target->reload();
		}
        
		$this->lock();
		
		$this->set(array(
			'left' => $target->{$copy_left_from} + $left_offset,
			'right' => $target->{$copy_left_from} + $left_offset + 1,
			'level' => $target->level + $level_offset,
			'scope' => $target->scope,
			));
        
		$this->create_space($this->left);
        
		try
		{
            $this->save();
		}
		catch (Exception $e)
		{
			// We had a problem creating - make sure we clean up the tree
			$this->delete_space($this->left);
			$this->unlock();
			throw $e;
		}
		
		$this->unlock();
		
		return $this;
	}
	
	/**
	 * Inserts a new node as the first child of the target node
	 *
	 * @access public
	 * @param Jelly_Model_MPTT|mixed $target target node primary key value or Jelly_MPTT object.
	 * @return  Jelly_Model_MPTT
	 */
	public function insert_as_first_child($target)
	{
		return $this->insert($target, 'left', 1, 1);
	}
	
	/**
	 * Inserts a new node as the last child of the target node
	 *
	 * @access public
	 * @param Jelly_Model_MPTT|mixed $target target node primary key value or Jelly_Model_MPTT object.
	 * @return Jelly_Model_MPTT
	 */
	public function insert_as_last_child($target)
	{
		return $this->insert($target, 'right', 0, 1);
	}

	/**
	 * Inserts a new node as a previous sibling of the target node.
	 *
	 * @access public
	 * @param Jelly_Model_MPTT|integer $target target node id or Jelly_Model_MPTT object.
	 * @return Jelly_Model_MPTT
	 */
	public function insert_as_prev_sibling($target)
	{
		return $this->insert($target, 'left', 0, 0);
	}

	/**
	 * Inserts a new node as the next sibling of the target node.
	 *
	 * @access public
	 * @param Jelly_Model_MPTT|integer $target target node id or Jelly_Model_MPTT object.
	 * @return Jelly_Model_MPTT
	 */
	public function insert_as_next_sibling($target)
	{	
		return $this->insert($target, 'right', 1, 0);
	}
	
	/**
	 * Overloaded create method
	 * 
	 * @access public
	 * @return void 
	 * @throws Validation_Exception
	 */
	public function create()
	{
		// Don't allow creation directly as it will invalidate the tree
		throw new Kohana_Exception('You cannot use create() on Jelly_MPTT model :name. Use an appropriate insert_* method instead',
				array(':name' => get_class($this)));
	}
	
	/**
	 * Removes a node and it's descendants.
	 *
	 * @access public
	 */
	public function delete_obj(Database_Query_Builder_Delete $query = NULL)
	{
		if ($query !== NULL)
		{
			throw new Kohana_Exception('Jelly_MPTT does not support passing a query object to delete()');
		}
		
		$this->lock();
		
		// Handle un-foreseen exceptions
		try
		{
			DB::delete($this->table)
				->where($this->_left_column, '>=', $this->left)
				->where($this->_right_column, '<=', $this->right)
				->where($this->_scope_column, '=', $this->scope)
				->execute($this->db);
			
			$this->delete_space($this->left, $this->get_size());
		}
		catch (Exception $e)
		{
			//Unlock table and re-throw exception
			$this->unlock();
			throw $e;
		}
		
		$this->unlock();
	}

	/**
	 * Overloads the select_list method to
	 * support indenting.
	 * 
	 * Returns all recods in the current scope
	 *
	 * @param string $key first table column.
	 * @param string $val second table column.
	 * @param string $indent character used for indenting.
	 * @return array 
	 */
	public function select_list($key = 'id', $value = 'name', $indent = NULL)
	{
		$result = DB::select($key, $value, $this->_level_column)
			->from($this->table)
			->where($this->_scope_column, '=',$this->scope)
			->order_by($this->_left_column, 'ASC')
			->execute($this->db);
			
		if (is_string($indent))
		{		
			$array = array();
			
			foreach ($result as $row)
			{
				$array[$row[$key]] = str_repeat($indent, $row[$this->_level_column]).$row[$value];
			}
			
			return $array;
		}

		return $result->as_array($key, $value);
	}
	
	/**
	 * Move to First Child
	 *
	 * Moves the current node to the first child of the target node.
	 *
	 * @param Jelly_Model_MPTT|integer $target target node id or Jelly_Model_MPTT object.
	 * @return Jelly_Model_MPTT
	 */
	public function move_to_first_child($target)
	{
		return $this->move($target, TRUE, 1, 1, TRUE);
	}
	
	/**
	 * Move to Last Child
	 *
	 * Moves the current node to the last child of the target node.
	 *
	 * @param Jelly_Model_MPTT|integer $target target node id or Jelly_Model_MPTT object.
	 * @return Jelly_Model_MPTT
	 */
	public function move_to_last_child($target)
	{	
		return $this->move($target, FALSE, 0, 1, TRUE);
	}
	
	/**
	 * Move to Previous Sibling.
	 *
	 * Moves the current node to the previous sibling of the target node.
	 *
	 * @param Jelly_Model_MPTT|integer $target target node id or Jelly_Model_MPTT object.
	 * @return Jelly_Model_MPTT
	 */
	public function move_to_prev_sibling($target)
	{	
		return $this->move($target, TRUE, 0, 0, FALSE);
	}
	
	/**
	 * Move to Next Sibling.
	 *
	 * Moves the current node to the next sibling of the target node.
	 *
	 * @param Jelly_Model_MPTT|integer $target target node id or Jelly_Model_MPTT object.
	 * @return Jelly_Model_MPTT
	 */
	public function move_to_next_sibling($target)
	{
		return $this->move($target, FALSE, 1, 0, FALSE);
	}
	
	/**
	 * Move
	 *
	 * @param Jelly_Model_MPTT|integer $target target node id or Jelly_Model_MPTT object.
	 * @param bool $left_column use the left column or right column from target
	 * @param integer $left_offset left value for the new node position.
	 * @param integer $level_offset level
	 * @param bool $allow_root_target allow this movement to be allowed on the root node
     * @return  Jelly_Model_MPTT|bool $this
	 */
	protected function move($target, $left_column, $left_offset, $level_offset, $allow_root_target)
	{
		if ( ! $this->loaded())
			return FALSE;
		
		// Make sure we have the most upto date version of this AFTER we lock
		$this->lock();
		$this->reload();
		
		// Catch any database or other excpetions and unlock
		try
		{
			if ( ! $target instanceof $this)
			{
				$target = Jelly::select($this)->load($target);
				
				if ( ! $target->loaded())
				{
					$this->unlock();
					return FALSE;
				}
			}
			else
			{
				$target->reload();
			}
			
			// Stop $this being moved into a descendant or itself or disallow if target is root
			if ($target->is_descendant($this) 
				OR $this->{$this->meta()->primary_key()} === $target->{$this->meta()->primary_key()}
				OR ($allow_root_target === FALSE AND $target->is_root()))
			{
				$this->unlock();
				return FALSE;
			}
			
			$left_offset = ($left_column === TRUE ? $target->left : $target->right) + $left_offset;
			$level_offset = $target->level - $this->level + $level_offset;
	
			$size = $this->get_size();
			
			$this->create_space($left_offset, $size);
	
			// if node is moved to a position in the tree "above" its current placement
			// then its lft/rgt may have been altered by create_space
			$this->reload();
			
			$offset = ($left_offset - $this->left);
			
			// Update the values.
			DB::update($this->table)
                ->set(array(
                    $this->_left_column => DB::expr('`'.$this->_left_column.'` + '.$offset),
                    $this->_right_column => DB::expr('`'.$this->_right_column.'` + '.$offset),
                    $this->_level_column => DB::expr('`'.$this->_level_column.'` + '.$level_offset),
                    $this->_scope_column => $target->scope
                    ))
                ->where($this->_left_column, '>=', $this->left)
                ->and_where($this->_right_column, '<=', $this->right)
                ->and_where($this->_scope_column, '=', $this->scope)
                ->execute($this->db);
			
			$this->delete_space($this->left, $size);
		}
		catch (Exception $e)
		{
			//Unlock table and re-throw exception
			$this->unlock();
			throw $e;
		}

		$this->unlock();
		
		return $this;
	}
	
	/**
	 *
	 * @access public
	 * @param $column - Which field to get.
	 * @return mixed
	 */
	public function __get($column)
	{
		switch ($column)
		{
			case 'parent':
				return $this->parent();
			case 'parents':
				return $this->parents();
			case 'children':
				return $this->children();
			case 'first_child':
				return $this->children(FALSE, 'ASC', 1);
			case 'last_child':
				return $this->children(FALSE, 'DESC', 1);
			case 'siblings':
				return $this->siblings();
			case 'root':
				return $this->root();
			case 'leaves':
				return $this->leaves();
			case 'descendants':
				return $this->descendants();
            /*case 'left_column':
                return $this->meta()->left_column;
            case 'right_column':
                return $this->meta()->right_column;
            case 'level_column':
                return $this->meta()->level_column;
            case 'scope_column':
                return $this->meta()->scope_column; */
            case 'db':
                return $this->meta()->db();
            case 'table':
                return $this->meta()->table();
			default:
				return parent::__get($column);
		}
	}
	
	/**
	 * Verify the tree is in good order 
	 * 
	 * This functions speed is irrelevant - its really only for debugging and unit tests
	 * 
	 * @todo Look for any nodes no longer contained by the root node.
	 * @todo Ensure every node has a path to the root via ->parents(); 
	 * @access public
	 * @return boolean
	 */
	public function verify_tree()
	{
		foreach ($this->get_scopes() as $scope)
		{
			if ( ! $this->verify_scope($scope->scope))
				return FALSE;
		}
		return TRUE;
	}
	
	private function get_scopes()
	{
		// TODO... redo this so its proper :P and open it public
		// used by verify_tree()
        //return DB::select()->as_object()->distinct($this->scope_column)->from($this->table)->execute($this->db);
                
        return DB::select($this->_scope_column)->distinct(TRUE)->from($this->table)->as_object()->execute($this->db);
	}
	
	public function verify_scope($scope)
	{   
		$root = $this->root($scope);
		
		$end = $root->right;
		
		// Find nodes that have slipped out of bounds.
		$count = DB::select(array('COUNT("*")', 'count'))
            ->from($this->table)
            ->where($this->_scope_column, '=', $root->scope)
            ->and_where_open($this->_left_column, '>', $end)
                ->or_where($this->_right_column, '>', $end)
            ->and_where_close()
            ->execute($this->db)
            ->get('count');
		if ($count > 0)
			return FALSE;
		
		// Find nodes that right value is less or equal as the left value
		$count = DB::select(array('COUNT("*")', 'count'))
            ->from($this->table)
            ->where($this->_scope_column, '=', $root->scope)
            ->and_where($this->_left_column, '>=', DB::expr('`'.$this->_right_column.'`'))
            ->execute($this->db)
            ->get('count');
        if ($count > 0)
            return FALSE;
		
		// Make sure no 2 nodes share a left/right value
		$i = 1;
		while ($i <= $end)
		{
			// TODO optimize request
            $result = Database::instance($this->db)->query(Database::SELECT, 'SELECT count(*) as count FROM `'.Database::instance($this->db)->table_prefix().$this->table.'` 
				WHERE `'.$this->_scope_column.'` = '.$root->scope.' 
				AND (`'.$this->_left_column.'` = '.$i.' OR `'.$this->_right_column.'` = '.$i.')', TRUE);
			
			if ($result[0]->count > 1)
				return FALSE;
				
			$i++;
		}
		
		// Check to ensure that all nodes have a "correct" level
		
		return TRUE;
	}
	
	/**
	 * Force object to reload MPTT fields from database
	 * 
	 * @return $this
	 */
	public function reload()
	{
		if ( ! $this->loaded()) 
		{
			return FALSE;
		}
		
		$mptt_vals = DB::select(
				$this->_left_column,
				$this->_right_column,
				$this->_level_column,
				$this->_scope_column
			)
			->from($this->table)
			->where($this->meta()->primary_key(), '=', $this->{$this->meta()->primary_key()})
			->execute($this->db)
			->current();
            
        //return $this->values($mptt_vals);
		return $this->set($mptt_vals);
	}
		
	/**
	 * Generates the HTML for this node's descendants
	 *
	 * @param string $style pagination style.
	 * @param boolean $self include this node or not.
	 * @param string $direction direction to order the left column by.
	 * @return View
	 */
	public function render_descendants($style = NULL, $self = FALSE, $direction = 'ASC')
	{
		$nodes = $this->descendants($self, $direction);
		
		if ($style === NULL)
		{
			$style = $this->_style;
		}

		return View::factory($this->_directory.DIRECTORY_SEPARATOR.$style, array('nodes' => $nodes,'level_column' => 'level'));
	}
	
	/**
	 * Generates the HTML for this node's children
	 *
	 * @param string $style pagination style.
	 * @param boolean $self include this node or not.
	 * @param string $direction direction to order the left column by.
	 * @return View
	 */
	public function render_children($style = NULL, $self = FALSE, $direction = 'ASC')
	{
		$nodes = $this->children($self, $direction);
		
		if ($style === NULL)
		{
			$style = $this->_style;
		}

		return View::factory($this->_directory.DIRECTORY_SEPARATOR.$style, array('nodes' => $nodes,'level_column' => 'level'));
	}
}