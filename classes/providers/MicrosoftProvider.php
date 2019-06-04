<?php

namespace Clake\UserExtended\Classes\Providers;

use Illuminate\Support\Arr;
use Laravel\Socialite\Two\ProviderInterface;
use SocialiteProviders\Manager\OAuth2\User;
use SocialiteProviders\Graph\Provider;

class MicrosoftProvider extends Provider {

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user) {
        return (new User())->setRaw($user)->map([
                    'id' => $user['id'],
                    'name' => $user['displayName'],
                    'email' => $user['mail'],
                    'nickname' => $user['displayName'],
        ]);
    }

}
