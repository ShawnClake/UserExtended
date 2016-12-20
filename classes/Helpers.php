<?php

namespace Clake\UserExtended\Classes;

class Helpers
{
    public static function unlimited($limit = 0)
    {
        if($limit == 0)
            return 18446744073709551610;
        return $limit;
    }
}