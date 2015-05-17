<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Token\AccessToken;

class Microsoft extends AbstractProvider
{
    public $scopes = ['wl.basic', 'wl.emails'];
    public $responseType = 'json';

    public function urlAuthorize()
    {
        return 'https://login.live.com/oauth20_authorize.srf';
    }

    public function urlAccessToken()
    {
        return 'https://login.live.com/oauth20_token.srf';
    }

    public function urlUserDetails(AccessToken $token)
    {
        return 'https://apis.live.net/v5.0/me?access_token='.$token;
    }

    public function userDetails($response, AccessToken $token)
    {
        $client = $this->getHttpClient();
        $client->setBaseUrl('https://apis.live.net/v5.0/'.$response->id.'/picture');
        $request = $client->get()->send();
        $info = $request->getInfo();
        $imageUrl = $info['url'];

        $user = new User();

        $email = (isset($response->emails->preferred)) ? $response->emails->preferred : null;

        $user->exchangeArray([
            'uid' => $response->id,
            'name' => $response->name,
            'firstname' => $response->first_name,
            'lastname' => $response->last_name,
            'email' => $email,
            'imageurl' => $imageUrl,
            'urls' => $response->link.'/cid-'.$response->id,
        ]);

        return $user;
    }

    public function userUid($response, AccessToken $token)
    {
        return $response->id;
    }

    public function userEmail($response, AccessToken $token)
    {
        return isset($response->emails->preferred) && $response->emails->preferred
            ? $response->emails->preferred
            : null;
    }

    public function userScreenName($response, AccessToken $token)
    {
        return [$response->first_name, $response->last_name];
    }
}
