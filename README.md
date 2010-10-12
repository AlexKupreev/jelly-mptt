# Jelly_MPTT

A Modified Preorder Tree Traversal extension for Kohana 3 [Jelly](http://github.com/jonathangeiger/kohana-jelly).

Ported from [Sprig_MPTT](http://github.com/banks/sprig-mptt) extension developed by Paul Banks.

Unit tests have been rebuilt for both Jelly and PHPUnit too.

### Differences From Sprig_MPTT

The vast majority of module API was preserved 'as is' without methods renaming etc. Only one method delete() was renamed as delete_obj() to fix unexpected fatal error while overriding (but I hope to fix that correctly a little bit later).

**Note** that you have to invoke parent::initialize($meta) at the end of Your_Model::initialize(Jelly_Meta $meta) method when extending Jelly_Model_MPTT. 

 	
### Versions

This is the modified version of Jelly_MPTT. Previous one had issues when custom extending Jelly_Meta so it has been rebuilt in more "Jellyish" style (thanks Paul Banks for commenting!). The "rebuilt" version is currently in *master* Git branch. Previous version is in *deprecated* branch and can still be used if you have no issues with it (really it was tested much better than the new one).

To port your code for the new version please note that it has 4 predefined fields: "left", "right", "level" and "scope". Database column names can be changed as well as other standard Jelly options.