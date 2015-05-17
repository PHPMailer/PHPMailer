<?php

namespace League\OAuth2\Client\Grant;

use League\OAuth2\Client\Token\AccessToken;

class ClientCredentials implements GrantInterface
{
    public function __toString()
    {
        return 'client_credentials';
    }

    public function prepRequestParams($defaultParams, $params)
    {
        $params['grant_type'] = 'client_credentials';

        return array_merge($defaultParams, $params);
    }

    public function handleResponse($response = array())
    {
        return new AccessToken($response);
    }
}
