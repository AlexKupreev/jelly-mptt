<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Jelly_MPTT PHPUnit Tests
 * 
 * ported from Sprig_MPTT by Paul Banks
 * 
 * @group kohana
 * @group kohana.jelly-mptt
 *
 * @package    Jelly_MPTT
 * @author     Paul Banks
 * @author Alexander Kupreyeu (Kupreev) (alexander dot kupreev at gmail dot com, http://kupreev.com)
 */
class JellyMPTT extends PHPUnit_Framework_TestCase {

	function __construct()
	{
		// Create test model database
        Jelly::factory('mptt_test')
			->create_table();
		
		/**
		 * Creates a table with the following trees
		 * 
		 * SCOPE 1
		 * 
		 *                   1[ 1 ]22
		 *                      |
		 *     -----------------------------------
		 *     |          |          |           |
		 *  2[ 2 ]3    4[ 3 ]7    8[ 5 ]9    10[ 6 ]21
		 *                |                      |
		 *             5[ 4 ]6      ---------------------------
		 *                          |            |            |
		 *                      11[ 7 ]12    13[ 8 ]18    19[ 11 ]20
		 *                                       |
		 *                                 --------------
		 *                                 |            |        
		 *                             14[ 9 ]15    16[ 10 ]17
		 *                             
		 * Then scopes 2 and 3 duplicate this structure with correspondingly higher IDs
		 * 
		 */
         
         // create tables for hasMany check
         Jelly::factory('mptt_owner')
            ->create_tables();
         
	}
	
	function teardown()
	{   
		Jelly::factory('mptt_test')
			->reset_table(); 
            
        Jelly::factory('mptt_owner')
            ->reset_tables();
	}
	
	function __destruct()
	{                         
		// Delete test model database
        try
        {
        	Jelly::factory('mptt_test')
			    ->delete_table();
        } 
        catch (Exception $e) 
        {
            echo $e->getMessage();
        }
        
        try
        {
            Jelly::factory('mptt_owner')
                ->delete_tables();    
        } 
        catch (Exception $e) 
        {
            echo $e->getMessage();
        }
        
	}
	
    public function testInsertAsNewRoot()
	{
		$model = Jelly::factory('mptt_test');
        $model->name = "Test Root Node";
        
        // Scope 1 should already exist
		$this->assertFalse($model->insert_as_new_root(1));
		// This should create scope 4 and return iteself
		$this->assertSame($model->insert_as_new_root(4), $model);
		// Check new root has been give the correct MPTT values
		$this->assertEquals($model->left, 1);
		$this->assertEquals($model->right, 2);
		$this->assertEquals($model->level, 0);
		$this->assertEquals($model->scope, 4);
		// Make sure we haven't invalidated the tree
		$this->assertTrue($model->verify_tree());  
            
	}
    
    public function testInsertAsNewRoot4HasMany()
    {
        $model = Jelly::factory('mptt_owned');
        $model->name = "Test Root Node";
        $model->owner = 2;
        
        // Scope 1 should already exist
        $this->assertFalse($model->insert_as_new_root(1));
        // This should create scope 4 and return iteself
        $this->assertSame($model->insert_as_new_root(4), $model);
        // Check new root has been give the correct MPTT values
        $this->assertEquals($model->left, 1);
        $this->assertEquals($model->right, 2);
        $this->assertEquals($model->level, 0);
        $this->assertEquals($model->scope, 4);
        // Make sure we haven't invalidated the tree
        $this->assertTrue($model->verify_tree());  
            
    }
	
	function testLoad()
	{
		
        $model = Jelly::select('mptt_test')
            ->where('id', '=',  3)
            ->limit(1)
            ->execute();
		                        
		$this->assertTrue($model->loaded());
		$this->assertEquals($model->left, 4);
		$this->assertEquals($model->right, 7);
	}
	
	function testRoot()
	{
		$root = Jelly::select('mptt_test')
            ->where('id', '=', 1)
            ->limit(1) 
            ->execute();
		$node = Jelly::select('mptt_test')
            ->where('id', '=', 8)
            ->limit(1) 
            ->execute();
		
		$this->assertTrue($root->loaded());
		$this->assertEquals($node->root()->id, $root->id);
	}
	
	function testHasChildren()
	{
		$node_with_one_child = Jelly::select('mptt_test')
            ->where('id', '=', 3)
            ->limit(1) 
            ->execute();
		$node_with_children = Jelly::select('mptt_test')
            ->where('id', '=', 8)
            ->limit(1) 
            ->execute();
		$leaf_node = Jelly::select('mptt_test')
            ->where('id', '=', 5)
            ->limit(1) 
            ->execute();
		
		$this->assertTrue($node_with_one_child->has_children());
		$this->assertFalse($node_with_one_child->is_leaf());
			
		$this->assertTrue($node_with_children->has_children());
		$this->assertFalse($node_with_children->is_leaf());
			
		$this->assertFalse($leaf_node->has_children());
		$this->assertTrue($leaf_node->is_leaf());
	}
	
	function testIsDescendant()
	{
		$node_3 = Jelly::select('mptt_test')
            ->where('id', '=', 3)
            ->limit(1) 
            ->execute();
		$node_4 = Jelly::select('mptt_test')
            ->where('id', '=', 4)
            ->limit(1) 
            ->execute();
		$root_node = $node_3->root();
		
		$this->assertTrue($node_3->is_descendant($root_node));
		$this->assertTrue($node_4->is_descendant($root_node));
		$this->assertTrue($node_4->is_descendant($node_3));
		$this->assertFalse($node_3->is_descendant($node_3));
		$this->assertFalse($node_3->is_descendant($node_4));
	}
	
	function testIsChild()
	{
		$node_3 = Jelly::select('mptt_test')
            ->where('id', '=', 3)
            ->limit(1) 
            ->execute();
		$node_4 = Jelly::select('mptt_test')
            ->where('id', '=', 4)
            ->limit(1) 
            ->execute();
		$node_5 = Jelly::select('mptt_test')
            ->where('id', '=', 4)
            ->limit(1) 
            ->execute();
            
		$root_node = $node_3->root();
		
		$this->assertTrue($node_4->is_child($node_3));
		$this->assertTrue($node_3->is_child($root_node));
		$this->assertFalse($node_4->is_child($root_node));
		$this->assertFalse($node_4->is_child($node_5));
		$this->assertFalse($node_4->is_child($node_4));
	}
	
	function testIsParent()
	{
		$node_3 = Jelly::select('mptt_test')
            ->where('id', '=', 3)
            ->limit(1) 
            ->execute();
		$node_4 = Jelly::select('mptt_test')
            ->where('id', '=', 4)
            ->limit(1) 
            ->execute();
		$node_5 = Jelly::select('mptt_test')
            ->where('id', '=', 4)
            ->limit(1) 
            ->execute();
		$root_node = $node_3->root();
		
		$this->assertTrue($node_3->is_parent($node_4));
		$this->assertTrue($root_node->is_parent($node_3));
		$this->assertFalse($root_node->is_parent($node_4));
		$this->assertFalse($node_4->is_parent($node_5));
		$this->assertFalse($node_4->is_parent($node_4));
	}
	
	function testIsSibling()
	{
		$node_5 = Jelly::select('mptt_test')
            ->where('id', '=', 5)
            ->limit(1) 
            ->execute();
		$node_6 = Jelly::select('mptt_test')
            ->where('id', '=', 6)
            ->limit(1) 
            ->execute();
		$node_7 = Jelly::select('mptt_test')
            ->where('id', '=', 7)
            ->limit(1) 
            ->execute();
		
		$this->assertTrue($node_5->is_sibling($node_6));
		$this->assertFalse($node_5->is_sibling($node_7));
		$this->assertFalse($node_5->is_sibling($node_5));
	}
	
	function testIsRoot()
	{
		$node_5 = Jelly::select('mptt_test')
            ->where('id', '=', 5)
            ->limit(1) 
            ->execute();
		$root = $node_5->root();
		
		$this->assertTrue($root->is_root());
		$this->assertFalse($node_5->is_root());
	}
	
	function testParent()
	{
		$node_5 = Jelly::select('mptt_test')
            ->where('id', '=', 5)
            ->limit(1) 
            ->execute();
		$root = $node_5->root();
		
		$this->assertEquals($node_5->parent(), $root);
		$this->assertFalse($root->parent()->loaded());
	}
	
	function testParents()
	{
		$node_5 = Jelly::select('mptt_test')
            ->where('id', '=', 5)
            ->limit(1) 
            ->execute();
		$node_9 = Jelly::select('mptt_test')
            ->where('id', '=', 9)
            ->limit(1) 
            ->execute();
		
		$node_5_parents = $node_5->parents();
		$node_9_parents = $node_9->parents();
		$node_9_parents_desc = $node_9->parents(TRUE, 'DESC');
		
		$this->assertTrue($node_5_parents instanceof Jelly_Collection);
		$this->assertEquals(count($node_5_parents), 1);
		$this->assertEquals(count($node_5->parents(FALSE)), 0);
		$this->assertEquals($node_5_parents[0]->id, 1);
			
		$this->assertTrue($node_9_parents instanceof Jelly_Collection);
		$this->assertEquals(count($node_9_parents), 3);
		$this->assertEquals(count($node_9->parents(FALSE)), 2);
		$this->assertEquals($node_9_parents[0]->id, 1);
		$this->assertEquals($node_9_parents[1]->id, 6);
		$this->assertEquals($node_9_parents[2]->id, 8);
			
		$this->assertEquals($node_9_parents_desc[2]->id, 1);
		$this->assertEquals($node_9_parents_desc[1]->id, 6);
		$this->assertEquals($node_9_parents_desc[0]->id, 8);
	}
	
	function testChildren()
	{
		$node_6 = Jelly::select('mptt_test')
            ->where('id', '=', 6)
            ->limit(1) 
            ->execute();

		$node_6_children = $node_6->children();
		$node_6_children_desc = $node_6->children(FALSE, 'DESC');
		
		$this->assertTrue($node_6_children instanceof Jelly_Collection);
		$this->assertEquals(count($node_6_children), 3);
		$this->assertEquals(count($node_6->children(TRUE)), 4);
		$this->assertEquals($node_6_children[0]->id, 7);
		$this->assertEquals($node_6_children[1]->id, 8);
		$this->assertEquals($node_6_children[2]->id, 11);
			
		$this->assertEquals($node_6_children_desc[0]->id, 11);
		$this->assertEquals($node_6_children_desc[1]->id, 8) ;
		$this->assertEquals($node_6_children_desc[2]->id, 7);
	}
	
	function testDescendants()
	{
		$node_6 = Jelly::select('mptt_test')
            ->where('id', '=', 6)
            ->limit(1) 
            ->execute();

		$node_6_descendants = $node_6->descendants();
		
		$this->assertTrue($node_6_descendants instanceof Jelly_Collection);
		$this->assertEquals(count($node_6_descendants), 5);
		$this->assertEquals(count($node_6->descendants(TRUE)), 6);
		$this->assertEquals($node_6_descendants[0]->id, 7);
		$this->assertEquals($node_6_descendants[1]->id, 8);
		$this->assertEquals($node_6_descendants[2]->id, 9);
		$this->assertEquals($node_6_descendants[3]->id, 10);
		$this->assertEquals($node_6_descendants[4]->id, 11);
	}
	
	function testSiblings()
	{	
		$node_5 = Jelly::select('mptt_test')
            ->where('id', '=', 5)
            ->limit(1) 
            ->execute();

		$node_5_siblings = $node_5->siblings();
		
		$this->assertTrue($node_5_siblings instanceof Jelly_Collection);
		$this->assertEquals(count($node_5_siblings), 3);
		$this->assertEquals(count($node_5->siblings(TRUE)), 4);
		$this->assertEquals($node_5_siblings[0]->id, 2);
		$this->assertEquals($node_5_siblings[1]->id, 3);
		$this->assertEquals($node_5_siblings[2]->id, 6);
	}
	
	function testLeaves()
	{
		$node_1 = Jelly::select('mptt_test')
            ->where('id', '=', 1)
            ->limit(1) 
            ->execute();

		$node_1_leaves = $node_1->leaves();
		
		$this->assertTrue($node_1_leaves instanceof Jelly_Collection);
        $this->assertEquals(count($node_1_leaves), 2);
		$this->assertEquals($node_1_leaves[0]->id, 2);
		$this->assertEquals($node_1_leaves[1]->id, 5);
	}

	function testInsertAsFirstChild()
	{
		$new = Jelly::factory('mptt_test');
		$new->name = 'Test Element';
		
		$node_3 = Jelly::select('mptt_test')
            ->where('id', '=', 3)
            ->limit(1) 
            ->execute();
		
		$this->assertFalse($node_3->insert_as_first_child(4));
		$this->assertEquals($new->insert_as_first_child($node_3), $new);
			
		// Reload node 3 to check insert worked
		$node_3 = Jelly::select('mptt_test')
            ->where('id', '=', 3)
            ->limit(1) 
            ->execute();
		$node_3_children = $node_3->children();
		
		$this->assertEquals(count($node_3_children), 2);
		$this->assertEquals($node_3_children[0]->id, $new->id);
		$this->assertTrue($node_3->verify_tree());
	}
    
    function testInsertAsFirstChild4HasMany()
    {
        $new = Jelly::factory('mptt_owned');
        $new->name = 'Test Element';
        $new->owner = 2;
        
        $node_1 = Jelly::select('mptt_owned')
            ->where('id', '=', 1)
            ->limit(1) 
            ->execute();
        
        //$this->assertFalse($node_1->insert_as_first_child(4));
        $this->assertEquals($new->insert_as_first_child($node_1), $new);
            
        // Reload node 1 to check insert worked
        $node_1 = Jelly::select('mptt_owned')
            ->where('id', '=', 1)
            ->limit(1) 
            ->execute();
        $node_1_children = $node_1->children();
        
        $this->assertEquals(count($node_1_children), 2);
        $this->assertEquals($node_1_children[0]->id, $new->id);
        $this->assertTrue($node_1->verify_tree());
    }

	function testInsertAsLastChild()
	{
		$new = Jelly::factory('mptt_test');
		$new->name = 'Test Element';
		
		$node_3 = Jelly::select('mptt_test')
            ->where('id', '=', 3)
            ->limit(1) 
            ->execute();
		
		$this->assertFalse($node_3->insert_as_last_child(4));
		$this->assertEquals($new->insert_as_last_child($node_3), $new);
			
		// Reload node 3 to check insert worked
		$node_3 = Jelly::select('mptt_test')
            ->where('id', '=', 3)
            ->limit(1) 
            ->execute();
		$node_3_children = $node_3->children();
		
		$this->assertEquals(count($node_3_children), 2);
		$this->assertEquals($node_3_children[1]->id, $new->id);
		$this->assertTrue($node_3->verify_tree());
	}

	function testInsertAsPrevSibling()
	{
		$new = Jelly::factory('mptt_test');
		$new->name = 'Test Element';
		
		$node_3 = Jelly::select('mptt_test')
            ->where('id', '=', 3)
            ->limit(1) 
            ->execute();
		
		$this->assertFalse($node_3->insert_as_prev_sibling(4));
		$this->assertEquals($new->insert_as_prev_sibling($node_3), $new);
			
		// Reload node 3 to check insert worked
		$node_3 = Jelly::select('mptt_test')
            ->where('id', '=', 3)
            ->limit(1) 
            ->execute();
		$node_3_siblings = $node_3->siblings(TRUE);
		
		$this->assertEquals(count($node_3_siblings), 5);
		$this->assertEquals($node_3_siblings[1]->id, $new->id);
		$this->assertTrue($node_3->verify_tree());
	}
    
    function testInsertAsPrevSibling4HasMany()
    {
        $new = Jelly::factory('mptt_owned');
        $new->name = 'Test Element';
        $new->owner = 2;
        
        $node_2 = Jelly::select('mptt_owned')
            ->where('id', '=', 2)
            ->limit(1) 
            ->execute();
        
        $this->assertEquals($new->insert_as_prev_sibling($node_2), $new);
            
        // Reload node 2 to check insert worked
        $node_2 = Jelly::select('mptt_owned')
            ->where('id', '=', 2)
            ->limit(1) 
            ->execute();
        $node_2_siblings = $node_2->siblings(TRUE);
        
        $this->assertEquals(count($node_2_siblings), 2);
        $this->assertEquals($node_2_siblings[0]->id, $new->id);
        $this->assertTrue($node_2->verify_tree());
    }

	function testInsertAsNextSibling()
	{
		$new = Jelly::factory('mptt_test');
		$new->name = 'Test Element';
		
		$node_3 = Jelly::select('mptt_test')
            ->where('id', '=', 3)
            ->limit(1) 
            ->execute();
		
		$this->assertFalse($node_3->insert_as_next_sibling(4));
		$this->assertEquals($new->insert_as_next_sibling($node_3), $new);
			
		// Reload node 3 to check insert worked
		$node_3 = Jelly::select('mptt_test')
            ->where('id', '=', 3)
            ->limit(1) 
            ->execute();
		$node_3_siblings = $node_3->siblings(TRUE);
		
		$this->assertEquals(count($node_3_siblings), 5);
		$this->assertEquals($node_3_siblings[2]->id, $new->id);
		$this->assertTrue($node_3->verify_tree());
	}
    
    function testDelete()
	{
		$node_3 = Jelly::select('mptt_test')
            ->where('id', '=', 3)
            ->limit(1) 
            ->execute();
		$node_3->delete_obj();
		
		$root = $node_3->root();
		$root_children = $root->children();
		
		$this->assertEquals(count($root_children), 3);
		$this->assertNotEquals($root_children[1]->id, 3);
		$this->assertTrue($node_3->verify_tree());
	}

	function testMoveToFirstChild()
	{
		$node_3 = Jelly::select('mptt_test')
            ->where('id', '=', 3)
            ->limit(1) 
            ->execute();
		
		$this->assertFalse($node_3->move_to_first_child(3));
		$this->assertEquals($node_3->move_to_first_child(6), $node_3);
		
		// Load node 6 to check move worked
		$node_6 = Jelly::select('mptt_test')
            ->where('id', '=', 6)
            ->limit(1) 
            ->execute();
		$node_6_children = $node_6->children();
		$root = $node_6->root();
		$root_children = $root->children();
		
		$this->assertEquals(count($node_6_children), 4);
		$this->assertEquals($node_6_children[0]->id, 3);
		$this->assertEquals(count($root_children), 3);
		$this->assertNotEquals($root_children[1]->id, 3);
		$this->assertTrue($node_3->verify_tree());
			
	}

	function testMoveToLastChild()
	{
		$node_3 = Jelly::select('mptt_test')
            ->where('id', '=', 3)
            ->limit(1) 
            ->execute();
		
		$this->assertFalse($node_3->move_to_last_child(3));
		$this->assertEquals($node_3->move_to_last_child(6), $node_3);
		
		// Load node 6 to check move worked
		$node_6 = Jelly::select('mptt_test')
            ->where('id', '=', 6)
            ->limit(1) 
            ->execute();
		$node_6_children = $node_6->children();
		$root = $node_6->root();
		$root_children = $root->children();

		$this->assertEquals(count($node_6_children), 4);
		$this->assertEquals($node_6_children[3]->id, 3);
		$this->assertEquals(count($root_children), 3);
		$this->assertNotEquals($root_children[1]->id, 3) ;
		$this->assertTrue($node_3->verify_tree());
	}

	function testMoveToPrevSibling()
	{
		$node_3 = Jelly::select('mptt_test')
            ->where('id', '=', 3)
            ->limit(1) 
            ->execute();
		
		$this->assertFalse($node_3->move_to_prev_sibling(3));
		$this->assertEquals($node_3->move_to_prev_sibling(8), $node_3);
		
		// Load node 8 to check move worked
		$node_8 = Jelly::select('mptt_test')
            ->where('id', '=', 8)
            ->limit(1) 
            ->execute();
		$node_8_siblings = $node_8->siblings(TRUE);
		$root = $node_8->root();
		$root_children = $root->children();

		$this->assertEquals(count($node_8_siblings), 4);
		$this->assertEquals($node_8_siblings[1]->id, 3);
		$this->assertEquals(count($root_children), 3);
		$this->assertNotEquals($root_children[1]->id, 3) ;
		$this->assertTrue($node_3->verify_tree());
	}
	
	function testMoveToNextSibling()
	{
		$node_3 = Jelly::select('mptt_test')
            ->where('id', '=', 3)
            ->limit(1) 
            ->execute();
		
		$this->assertFalse($node_3->move_to_next_sibling(3));
		$this->assertEquals($node_3->move_to_next_sibling(8), $node_3);
		
		// Load node 8 to check move worked
		$node_8 = Jelly::select('mptt_test')
            ->where('id', '=', 8)
            ->limit(1) 
            ->execute();
		$node_8_siblings = $node_8->siblings(TRUE);
		$root = $node_8->root();
		$root_children = $root->children();

		$this->assertEquals(count($node_8_siblings), 4);
		$this->assertEquals($node_8_siblings[2]->id, 3);
		$this->assertEquals(count($root_children), 3);
		$this->assertNotEquals($root_children[1]->id, 3);
		$this->assertTrue($node_3->verify_tree());
	}

	function testFirstChild()
	{
		$node_6 = Jelly::select('mptt_test')
            ->where('id', '=', 6)
            ->limit(1) 
            ->execute();
		$first_child = $node_6->first_child;
		
		$this
			->assertEquals($first_child->id, 7);
	}

	function testLastChild()
	{
		$node_6 = Jelly::select('mptt_test')
            ->where('id', '=', 6)
            ->limit(1) 
            ->execute();
		$last_child = $node_6->last_child;
		
		$this
			->assertEquals($last_child->id, 11);
	}

} // End Jelly_MPTT test class