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
}