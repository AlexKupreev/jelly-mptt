<?php defined('SYSPATH') or die('No direct script access.');

abstract class Jelly extends Jelly_Core
{
    /**
     * Returns the builder class to use for the specified model
     *
     * @param   string  $model
     * @param   int     $type
     * @return  string
     */
    protected static function builder($model, $type = NULL)
    {
        if ( ! is_object($model))
        {
            $model = Jelly::factory($model);
        }

        return parent::builder($model, $type);
    }
}