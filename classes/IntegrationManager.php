<?php namespace Clake\UserExtended\Classes;

use Clake\Userextended\Models\IntegratedUser;

/**
 * Class IntegrationManager
 * @package Clake\UserExtended\Classes
 */
class IntegrationManager
{
    const UE_INTEGRATIONS_FACEBOOK = 'UE_INTEGRATIONS_FACEBOOK';
    const UE_INTEGRATIONS_DISQUS = 'UE_INTEGRATIONS_DISQUS';

    public static function getUser($integrationId)
    {
        if(IntegratedUser::where('integration_id', $integrationId)->count() <= 0)
            return null;

        return IntegratedUser::where('integration_id', $integrationId)->first()->user;
    }

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