<?php

namespace Clake\UserExtended\Classes\Providers;

use Illuminate\Support\Arr;
use Laravel\Socialite\Two\ProviderInterface;
use SocialiteProviders\Manager\OAuth2\User;
use SocialiteProviders\Mixer\Provider;

class MixerProvider extends Provider {

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user) {
        return (new User())->setRaw($user)->map([
            'id'        => Arr::get($user, 'id'),
            'nickname'  => Arr::get($user, 'username'),
            'email'     => Arr::get($user, 'email'),
            'avatar_original'    => Arr::get($user, 'avatarUrl'),
        ]);
    }

}
