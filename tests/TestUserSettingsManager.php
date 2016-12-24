<?php

namespace Clake\UserExtended\Tests;

use Clake\UserExtended\Classes\UserSettingsManager;

class TestUserSettingsManager
{

    public static function test()
    {
        $yaml = UserSettingsManager::init()->yamlCheck();
        //$setit = UserSettingsManager::init()->setSetting('phone', '333-333-3333')->save();
        $settings = UserSettingsManager::init()->all();
        //$setit = UserSettingsManager::init()->setSetting('phone', '333-444-3333')->save();
        $setit = UserSettingsManager::init()->setSetting('address', 'moron road')->save();
        $getit = UserSettingsManager::init()->getSetting('address');
        $user = UserSettingsManager::init()->userCheck();
        var_dump($settings);
        return [true, var_export($settings, true)];
    }

}