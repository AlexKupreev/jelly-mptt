# Jelly_MPTT

A Modified Preorder Tree Traversal extension for Kohana 3 [Jelly](http://github.com/jonathangeiger/kohana-jelly).

Ported from [Sprig_MPTT](http://github.com/banks/sprig-mptt) extension developed by Paul Banks.

Unit tests have been rebuilt for both Jelly and PHPUnit too.

### Differences From Sprig_MPTT

The vast majority of module API was preserved 'as is' without methods renaming etc. Only one method delete() was renamed as delete_obj() to fix unexpected fatal error while overriding (but I hope to fix that correctly a little bit later).

**Note** that you have to invoke parent::initialize($meta) at the end of Your_Model::initialize($meta) method when extending Jelly_Model_MPTT. 

 	
	
	
	
	
	
	
	
	
