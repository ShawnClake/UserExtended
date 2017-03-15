<?php namespace Clake\UserExtended\Traits;

use Carbon\Carbon;
use Clake\UserExtended\Classes\TimezoneHandler;
use Clake\UserExtended\Classes\UserUtil;
use Clake\Userextended\Models\UserExtended;
use Exception;

/**
 * User Extended by Shawn Clake
 * Class Timezonable
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\UserExtended\Traits
 *
 * Adds automated Timezone adjustments to models via the class variable 'timezonable'
 */
trait Timezonable
{

    /**
     * Returns a timestamp adjusted by the logged in users Timezone
     * @param $timestamp
     * @param UserExtended|null $user
     * @return mixed
     */
    public function getTime($timestamp, UserExtended $user = null)
    {
        if ($user == null)
            $timezone = UserUtil::getLoggedInUsersTimezone();
        else
            $timezone = UserUtil::getUserTimezone($user->id);

        if($timezone == null)
            $timezone = TimezoneHandler::getUTCTimezone();

        $timestamp = new Carbon($timestamp);

        return TimezoneHandler::getTimeAdjustedByTimezone($timestamp, $timezone);
    }

    /**
     * Returns a timestamp adjusted by the logged in users Timezone
     * Alias for getTime()
     * @param $timestamp
     * @return mixed
     */
    public function timezonify($timestamp)
    {
        return $this->getTime($timestamp);
    }

    /**
     * Called by the system on runtime, Binds an event to the model to adjust timezones
     * @throws Exception
     */
    public static function bootTimezonable()
    {
        if (!property_exists(get_called_class(), 'timezonable')) {
            throw new Exception(sprintf(
                'You must define a $timezonable property in %s to use the Timezonable trait.', get_called_class()
            ));
        }

        /*
         * Timezone required fields when necessary
         */
        static::extend(function($model) {
            $timezonable = $model->getTimezonableAttributes();
            $model->bindEvent('model.beforeGetAttribute', function($key) use ($model, $timezonable) {
                if (in_array($key, $timezonable) && array_get($model->attributes, $key) != null) {
                    return $model->timezonify($model->attributes[$key]);
                }
            });
        });
    }

    /**
     * Returns a collection of fields that will be encrypted.
     * @return array
     */
    public function getTimezonableAttributes()
    {
        return $this->timezonable;
    }

}