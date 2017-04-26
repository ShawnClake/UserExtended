<?php namespace Clake\UserExtended\Classes;

use Clake\Userextended\Models\IntegratedUser;

/**
 * User Extended by Shawn Clake
 * Class IntegrationManager
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\UserExtended\Classes
 */
class IntegrationManager
{
    /**
     * Integration Types
     */
    const UE_INTEGRATIONS_FACEBOOK = 'UE_INTEGRATIONS_FACEBOOK';
    const UE_INTEGRATIONS_DISQUS = 'UE_INTEGRATIONS_DISQUS';

    /**
     * Retrieves a user integration
     * @param $integrationId
     * @return null
     */
    public static function getUser($integrationId)
    {
        if(IntegratedUser::where('integration_id', $integrationId)->count() <= 0)
            return null;

        return IntegratedUser::where('integration_id', $integrationId)->first()->user;
    }

    /**
     * Creates a user integration
     * @param $integrationId
     * @param $userId
     * @param $type
     * @return IntegratedUser
     */
    public static function createUser($integrationId, $userId, $type)
    {
        $integration = new IntegratedUser();
        $integration->user_id = $userId;
        $integration->integration_id = $integrationId;
        $integration->type = $type;
        $integration->save();

        return $integration;
    }

}