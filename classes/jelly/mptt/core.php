<?php defined('SYSPATH') or die('No direct script access.');

/**
 * override Jelly_Core to prevent collision when extending Jelly_Meta class 
 * of original Jelly in other module or application
 * 
 * @package Jelly_MPTT
 */
class Jelly_MPTT_Core extends Jelly_Core
{
    /**
     * Gets a particular set of metadata about a model. If the model
     * isn't registered, it will attempt to register it.
     *
     * FALSE is returned on failure.
     *
     * @param   string|Jelly_Model_MPTT  $model
     * @return  Jelly_Meta_MPTT
     */
    public static function meta($model)
    {
        $model = Jelly_MPTT::model_name($model);

        if ( ! isset(Jelly_MPTT::$_models[$model]))
        {
            if ( ! Jelly_MPTT::register($model))
            {
                return FALSE;
            }
        }

        return Jelly_MPTT::$_models[$model];
    }
    
    
    /**
     * Automatically loads a model, if it exists,
     * into the meta table.
     *
     * Models are not required to register
     * themselves; it happens automatically.
     *
     * @param   string  $model
     * @return  boolean
     */
    public static function register($model)
    {
        $class = Jelly_MPTT::class_name($model);
        $model = Jelly_MPTT::model_name($model);

        // Don't re-initialize!
        if (isset(Jelly_MPTT::$_models[$model]))
        {
            return TRUE;
        }

         // Can we find the class?
        if (class_exists($class))
        {
            // Prevent accidentally trying to load ORM or Sprig models
            if ( ! is_subclass_of($class, "Jelly_Model"))
            {
                return FALSE;
            }
        }
        else
        {
            return FALSE;
        }

        // Load it into the registry
        Jelly_MPTT::$_models[$model] = $meta = new Jelly_Meta_MPTT($model);

        // Let the intialize() method override defaults.
        call_user_func(array($class, 'initialize'), $meta);

        // Finalize the changes
        $meta->finalize($model);

        return TRUE;
    }
}