<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Jelly_Meta extends Jelly_Meta_Core object to add MPTT properties.
 *
 * @package Jelly_MPTT
 */
class Jelly_Meta extends Jelly_Meta_Core
{
    /**
     * @access public
     * @var string left column name.
     */
    public $left_column = NULL;
    
    /**
     * @access public
     * @var string right column name.
     */
    public $right_column = NULL;
    
    /**
     * @access public
     * @var string level column name.
     */
    public $level_column = NULL;
    
    /**
     * @access public
     * @var string scope column name.
     **/
    public $scope_column = NULL;

}