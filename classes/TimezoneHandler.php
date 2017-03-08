<?php namespace Clake\UserExtended\Classes;

use Carbon\Carbon;
use Clake\Userextended\Models\Timezone;

/**
 * User Extended by Shawn Clake
 * Class TimezoneHandler
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\UserExtended\Classes
 */
class TimezoneHandler
{

    public static function getCurrentTime()
    {
        return Carbon::now();
    }

    /**
     * Adjust the current UTC time by minutes, hours, days, and seconds
     * @param $minutes
     * @param int $hours
     * @param int $days
     * @param int $seconds
     * @return Carbon
     */
    public static function getCurrentTimeAdjusted($minutes, $hours = 0, $days = 0, $seconds = 0)
    {
        $current = self::getCurrentTime();
        $current->addHours($hours);
        $current->addMinutes($minutes);
        $current->addDays($days);
        $current->addSeconds($seconds);
        return $current;
    }

    /**
     * Take a time string such as 2:30 or 4:00 and convert it into minutes and hours
     * @param $time
     * @return array
     */
    private static function getTimeStringAdjustments($time)
    {
        $time = explode(":", $time);
        $minutes = 0;
        $hours = $time[0];
        if(isset($time[1]))
        {
            $minutes = $time[1];
            if($hours < 0)
                $minutes *= -1;
        }

        return [
            'minutes' => $minutes,
            'hours' => $hours,
        ];
    }

    /**
     * Get the current time adjusted via the logged in Users timezone
     * @return Carbon
     */
    public static function getLoggedInUsersCurrentTimeAdjusted()
    {
        $offset = UserUtil::getLoggedInUsersTimezone()->offset;
        $adjustment = self::getTimeStringAdjustments($offset);
        return self::getCurrentTimeAdjusted($adjustment['minutes'], $adjustment['hours']);
    }

    /**
     * Gets the current time adjusted via a Users timezone
     * @param $user
     * @return Carbon
     */
    public static function getUsersCurrentTimeAdjusted($user)
    {
        $offset = $user->timezone->offset;
        $adjustment = self::getTimeStringAdjustments($offset);
        return self::getCurrentTimeAdjusted($adjustment['minutes'], $adjustment['hours']);
    }

    /**
     * Gets a time adjusted via a Timezone model.
     * @param $time
     * @param Timezone $timezone
     * @return mixed
     */
    public static function getTimeAdjustedByTimezone($time, Timezone $timezone)
    {
        $offset = $timezone->offset;
        $adjustment = self::getTimeStringAdjustments($offset);
        return self::getTimeAdjusted($time, $adjustment['minutes'], $adjustment['hours']);
    }

    /**
     * Adjusts an arbitrary time by minutes, hours, days, and seconds
     * @param $time
     * @param $minutes
     * @param int $hours
     * @param int $days
     * @param int $seconds
     * @return mixed
     */
    public static function getTimeAdjusted($time, $minutes, $hours = 0, $days = 0, $seconds = 0)
    {
        $time->addHours($hours);
        $time->addMinutes($minutes);
        $time->addDays($days);
        $time->addSeconds($seconds);
        return $time;
    }

    /**
     * Get the time adjustment for the timezone of the currently logged in user.
     * @param $time
     * @return mixed
     */
    public static function getLoggedInUsersTimeAdjusted($time)
    {
        $offset = UserUtil::getLoggedInUsersTimezone()->offset;
        $adjustment = self::getTimeStringAdjustments($offset);
        return self::getTimeAdjusted($time, $adjustment['minutes'], $adjustment['hours']);
    }

    /**
     * Handle the Twig filter for allowing us to automatically reformat dates for the appropriate timezone
     * @param $time
     * @return mixed
     */
    public static function twigTimezoneAdjustment($time)
    {
        return self::getLoggedInUsersTimeAdjusted($time);
    }

    /**
     * Return the default Timezone
     * @return mixed
     */
    public static function getUTCTimezone()
    {
        return Timezone::where('id', 1)->first();
    }

    /**
     * The passed in time string should be in the applications default timezone.
     * By default this is UTC.
     * @param Carbon $time
     * @param string $locale
     * @return mixed
     */
    public static function getRelativeTimeString(Carbon $time, $locale = 'en')
    {
        // Doesn't need to be adjusted, because otherwise the diff in time is the same
        Carbon::setLocale($locale);
        return $time->diffForHumans();
    }

    public static function twigRelativeTimeString($time, $locale = 'en')
    {
        return self::getRelativeTimeString($time, $locale);
    }


}