<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Token\AccessToken as AccessToken;

interface ProviderInterface
{
    public function urlAuthorize();

    public function urlAccessToken();

    public function urlUserDetails(AccessToken $token);

    public function userDetails($response, AccessToken $token);

    public function getScopes();

    public function setScopes(array $scopes);

    public function getAuthorizationUrl($options = []);

    public function authorize($options = []);

    public function getAccessToken($grant = 'authorization_code', $params = []);

    public function getHeaders($token = null);

    public function getUserDetails(AccessToken $token);

    public function getUserUid(AccessToken $token);

    public function getUserEmail(AccessToken $token);

    public function getUserScreenName(AccessToken $token);
}
