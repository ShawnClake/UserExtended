<?php namespace Clake\UserExtended\Classes;

use Clake\Userextended\Models\UserExtended;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use \October\Rain\Support\Facades\Yaml;

/**
 * User Extended by Shawn Clake
 * Class UserSettingsManager
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\UserExtended\Classes
 *
 * Terminology and flow:
 *   A user has many settings.
 *   A setting has many finite options.
 *   Options have default values below.
 *   A setting has a value.
 *   An option has a state.
 */
class UserSettingsManager
{

    /**
     * Settings config file
     * @var array
     */
    protected $settingsTemplate = [];

    /**
     * Settings column from the user object
     * @var
     */
    protected $settings;

    /**
     * Stores the user object
     * @var null
     */
    protected $user = null;

    /**
     * Setting option defaults
     * @var array
     */
    private $defaults = [
        'label' => '',
        'type' => 'text',
        'validation' => '',
        'editable' => true,
        'createable' => true,
        'registerable' => false,
        'encrypt' => false,
    ];

    /**
     * Creates an instance of the UserSettingsManager
     * @param UserExtended|null $user
     * @return null|static
     */
    public static function init(UserExtended $user = null)
    {
        $instance = new static;
        $path = plugins_path('clake/userextended/config/user_settings.yaml');
        $settingsTemplate = Yaml::parseFile($path);

        if(isset($settingsTemplate['settings']))
            $instance->settingsTemplate = $settingsTemplate['settings'];
        else
            return null;

        if($user == null)
        {
            $user = UserUtil::getLoggedInUser();

            if(!$user == null)
                $user = UserUtil::convertToUserExtendedUser($user);
            else
                return $instance;
        }

        $instance->user = $user;

        $instance->settings = $instance->user->settings;

        return $instance;
    }

    /**
     * Returns the user settings on the user instance
     * Useful for debugging and tests
     * @return mixed
     */
    public function userSettingsCheck()
    {
        return $this->settings;
    }

    /**
     * Returns the config file contents
     * Useful for debugging and tests
     * @return array
     */
    public function yamlCheck()
    {
        return $this->settingsTemplate;
    }

    /**
     * Returns the user instance
     * Useful for debugging and tests
     * @return null
     */
    public function userCheck()
    {
        return $this->user;
    }

    /**
     * Determines whether the passed string is a valid setting according to the config
     * @param $setting
     * @return bool
     */
    public function isSetting($setting)
    {
        return array_key_exists($setting, $this->settingsTemplate);
    }

    /**
     * Gets the setting's options prioritizing config and then defaults
     * @param $setting
     * @return array|void
     */
    public function getSettingOptions($setting)
    {
        if(!$this->isSetting($setting))
            return false;

        $options = $this->settingsTemplate[$setting];

        return $this->mergeOptionsWithDefaults($options);
    }

    /**
     * Helper function for merging the config options with defaults
     * @param $options
     * @return array
     */
    public function mergeOptionsWithDefaults($options)
    {
        return array_merge($this->defaults, $options);
    }

    /**
     * Gets the value of a setting on a user model
     * @param $setting
     * @return mixed|string
     */
    public function getSettingValue($setting)
    {
        $value = '';

        if(isset($this->settings[$setting]))
            $value = $this->settings[$setting];

        return $value;
    }

    /**
     * Returns an array in the form of [value, options=>[]] for a setting on a user model
     * @param $setting
     * @return array|null
     */
    public function getSetting($setting)
    {
        if(!$this->isSetting($setting))
            return null;

        $value = $this->getSettingValue($setting);

        $value = $this->decrypt($setting, $value);

        $options = $this->getSettingOptions($setting);

        return [$value, 'options' => $options];
    }

    /**
     * Returns an array in the form of [setting1=>[value. options=>[]], setting2=>[value. options=>[]]]
     * representing all of the settings on a user model
     * @return array
     */
    public function all()
    {
        $settings = [];

        foreach($this->settingsTemplate as $key=>$setting)
        {
            $options = $this->getSettingOptions($key);

            $value = '';

            if(isset($this->settings[$key]))
            {
                $value = $this->settings[$key];
                if($this->isEncrypted($key))
                    $value = $this->decrypt($key, $value);
            }


            $settings[$key] = [$value, 'options' => $options];
        }

        return $settings;
    }

    /**
     * Returns whether or not a setting should exist on an update form page
     * @param $setting
     * @return bool
     */
    public function isEditable($setting)
    {
        $options = $this->getSettingOptions($setting);
        return $options['editable'];
    }

    /**
     * Returns whether or not a setting can be updated or created, Overrides both editable and registerable
     * @param $setting
     * @return mixed
     */
    public function isCreateable($setting)
    {
        $options = $this->getSettingOptions($setting);
        return $options['createable'];
    }

    /**
     * Returns whether a setting should exist on a sign up form
     * @param $setting
     * @return mixed
     */
    public function isRegisterable($setting)
    {
        $options = $this->getSettingOptions($setting);
        return $options['registerable'];
    }

    /**
     * Returns whether or not a setting has validation rules
     * @param $setting
     * @return bool
     */
    public function isValidated($setting)
    {
        $options = $this->getSettingOptions($setting);
        return $options['validation'] != '' && isset($options['validation']);
    }

    /**
     * Returns whether or not a setting should be encrypted
     * @param $setting
     * @return bool
     */
    public function isEncrypted($setting)
    {
        $options = $this->getSettingOptions($setting);
        return $options['encrypt'];
    }

    /**
     * Returns whether or not a passed value passes its validation rules
     * Will return true if the setting does not require validation
     * @param $setting
     * @param $value
     * @return bool
     */
    public function validate($setting, $value)
    {
        $options = $this->getSettingOptions($setting);

        if($this->isValidated($setting))
        {
            $validator = Validator::make(
                ['setting' => $value],
                ['setting' => $options['validation']]
            );

            if($validator->fails())
                return false;
        }

        return true;
    }

    /**
     * Returns an encrypted version of the passed value.
     * It will return the NON encrypted value if encryption is not required for the setting
     * @param $setting
     * @param $value
     * @return mixed
     */
    public function encrypt($setting, $value)
    {
        if($this->isEncrypted($setting))
        {
            $value = Crypt::encrypt($value);
        }

        return $value;
    }

    /**
     * Returns the decrypted version of the passed value
     * It will return the value if encryption is not required
     * @param $setting
     * @param $value
     * @return mixed
     */
    public function decrypt($setting, $value)
    {
        if($this->isEncrypted($setting))
        {
            $value = Crypt::decrypt($value);
        }

        return $value;
    }

    /**
     * Sets a setting by checking whether or not it can be edited, then validates it, then encrypts it if requried.
     * @param $setting
     * @param $value
     * @return $this|bool
     */
    public function setSetting($setting, $value)
    {
        if(!$this->validate($setting, $value))
            return false;

        $value = $this->encrypt($setting, $value);

        if($this->settings == "Array" || is_null($this->settings) || empty($this->settings))
            $this->settings = [];

        $this->settings[$setting] = $value;

        return $this;
    }

    /**
     * Save the settings to the user model
     * @return $this
     */
    public function save()
    {
        UserExtended::where('id', $this->user->id)->update(['settings'=>json_encode($this->settings)]);
        return $this;
    }

    /**
     * Returns an array of setting values and options for each setting marked with the option 'editable'
     * @return array
     */
    public function getUpdateable()
    {
        $settings = [];

        foreach($this->settingsTemplate as $key=>$setting)
        {
            if(!$this->isCreateable($key))
                continue;

            if(!$this->isEditable($key))
                continue;

            $options = $this->getSettingOptions($key);

            $value = '';

            if(isset($this->settings[$key]))
            {
                $value = $this->settings[$key];
                if($this->isEncrypted($key))
                    $value = $this->decrypt($key, $value);
            }

            $settings[$key] = [$value, 'options' => $options];
        }

        return $settings;
    }

    /**
     * Returns an array of setting values and options for each setting marked with the option 'registerable'
     * @return array
     */
    public function getRegisterable()
    {
        $settings = [];

        foreach($this->settingsTemplate as $key=>$setting)
        {
            if(!$this->isCreateable($key))
                continue;

            if(!$this->isRegisterable($key))
                continue;

            $options = $this->getSettingOptions($key);

            $settings[$key] = ['options' => $options];
        }

        return $settings;
    }

}