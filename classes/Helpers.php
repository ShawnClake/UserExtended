<?php namespace Clake\UserExtended\Classes;

/**
 * User Extended by Shawn Clake
 * Class Helpers
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\UserExtended\Classes
 */
class Helpers
{
    /**
     * Returns the max int which SQL can handle. This is useful for returning 'unlimited' results
     * @param int $limit
     * @return int
     */
    public static function unlimited($limit = 0)
    {
        if($limit == 0)
            return 18446744073709551610;
        return $limit;
    }

    /**
     * Returns the value at a key in an array or a default value if that key is empty or doesn't exist
     * @param array $array
     * @param $key
     * @param string $default
     * @return mixed|string
     */
    public static function arrayKeyToVal(array $array, $key, $default = '')
    {
        if(key_exists($key, $array) && isset($array[$key]) && !empty($array[$key]))
            return $array[$key];
        return $default;
    }

    /**
     * Returns an int with the highest bit in input as the only bit which is set
     * Function example taken from Hacker's Delight
     * @param $n
     * @return int
     */
    public static function hiBit($n)
    {
        $n |= ($n >>  1);
        $n |= ($n >>  2);
        $n |= ($n >>  4);
        $n |= ($n >>  8);
        $n |= ($n >> 16);
        $n |= ($n >> 32);
        return $n ^ ($n >> 1);
    }

    /**
     * Returns true if $bit is set inside of $bits
     * Both $bits and $bit are integers
     * Returns false if the bit is not set
     * @param $bits
     * @param $bit
     * @return bool
     */
    public static function isBitSet($bits, $bit)
    {
        return !!((int)$bits & (int)$bit);
    }

    /**
     * Uses the desired delete type. Useful in cases where you need to dynamically
     * determine whether a model should be hard deleted or soft deleted
     * @param $model
     * @param bool $forceDelete
     */
    public static function deleteModel($model, $forceDelete = false)
    {
        if($forceDelete === false)
            $model->forceDelete();
        else
            $model->delete();
    }

}