<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Token\AccessToken;

class Github extends AbstractProvider
{
    public $responseType = 'string';

    public $authorizationHeader = 'token';

    public $domain = 'https://github.com';

    public $apiDomain = 'https://api.github.com';

    public function urlAuthorize()
    {
        return $this->domain.'/login/oauth/authorize';
    }

    public function urlAccessToken()
    {
        return $this->domain.'/login/oauth/access_token';
    }

    public function urlUserDetails(AccessToken $token)
    {
        if ($this->domain === 'https://github.com') {
            return $this->apiDomain.'/user';
        }
        return $this->domain.'/api/v3/user';
    }

    public function urlUserEmails(AccessToken $token)
    {
        if ($this->domain === 'https://github.com') {
            return $this->apiDomain.'/user/emails';
        }
        return $this->domain.'/api/v3/user/emails';
    }

    public function userDetails($response, AccessToken $token)
    {
        $user = new User();

        $name = (isset($response->name)) ? $response->name : null;
        $email = (isset($response->email)) ? $response->email : null;

        $user->exchangeArray([
            'uid' => $response->id,
            'nickname' => $response->login,
            'name' => $name,
            'email' => $email,
            'urls'  => [
                'GitHub' => $this->domain.'/'.$response->login,
            ],
        ]);

        return $user;
    }

    public function userUid($response, AccessToken $token)
    {
        return $response->id;
    }

    public function getUserEmails(AccessToken $token)
    {
        $response = $this->fetchUserEmails($token);

        return $this->userEmails(json_decode($response), $token);
    }

    public function userEmail($response, AccessToken $token)
    {
        return isset($response->email) && $response->email ? $response->email : null;
    }

    public function userEmails($response, AccessToken $token)
    {
        return $response;
    }

    public function userScreenName($response, AccessToken $token)
    {
        return $response->name;
    }

    protected function fetchUserEmails(AccessToken $token)
    {
        $url = $this->urlUserEmails($token);

        $headers = $this->getHeaders($token);

        return $this->fetchProviderData($url, $headers);
    }
}
