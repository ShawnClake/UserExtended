<?php namespace Clake\Userextended\Models;

use Clake\UserExtended\Classes\GroupManager;
use Lang;
use Model;

/**
 * User Extended by Shawn Clake
 * Class Settings
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\Userextended\Models
 */
class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'clake_userextended_settings';

    public $settingsFields = 'fields.yaml';

    /**
     * Sets the defaults for settings
     */
    public function initSettingsData()
    {
        $this->validation_username = 'required|between:2,255';
        $this->validation_password = 'required|between:4,255|confirmed';
        $this->enable_profiles = true;
        $this->enable_friends = true;
        $this->enable_timezones = true;
        $this->enable_groups = true;
        $this->enable_email = true;
        $this->default_timezone = "UTC";
        $this->default_group = '';
        $this->enable_disqus = false;
        $this->enable_facebook = false;
        $this->disqus_shortname = '';
        $this->facebook_appid = '';
    }

    /**
     * Returns all groups for creating a dropdown list in order to choose the default group
     * @param $values
     * @param $formData
     * @return array
     */
    public function getDefaultGroupOptions($values, $formData)
    {
        $groups = GroupManager::allGroups()->getGroups();

        $options = [
            '' => '-- None --',
        ];

        foreach($groups as $group)
        {
            $options[$group->code] = $group->name;
        }

        return $options;
    }

    /**
     * Returns all timezones for creating a dropdown list in order to choose the default group
     * @param $values
     * @param $formData
     * @return array
     */
    public function getDefaultTimezoneOptions($values, $formData)
    {
        return Timezone::getTimezonesList();
    }


}