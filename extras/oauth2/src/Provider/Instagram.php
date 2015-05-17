<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;

class Instagram extends AbstractProvider
{
    public $scopes = ['basic'];
    public $responseType = 'json';

    public function urlAuthorize()
    {
        return 'https://api.instagram.com/oauth/authorize';
    }

    public function urlAccessToken()
    {
        return 'https://api.instagram.com/oauth/access_token';
    }

    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://api.instagram.com/v1/users/self?access_token='.$token;
    }

    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $user = new User();

        $description = (isset($response->data->bio)) ? $response->data->bio : null;

        $user->exchangeArray([
            'uid' => $response->data->id,
            'nickname' => $response->data->username,
            'name' => $response->data->full_name,
            'description' => $description,
            'imageUrl' => $response->data->profile_picture,
        ]);

        return $user;
    }

    public function userUid($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $response->data->id;
    }

    public function userEmail($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return;
    }

    public function userScreenName($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $response->data->full_name;
    }
}
