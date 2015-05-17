<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Token\AccessToken;

class LinkedIn extends AbstractProvider
{
    public $scopes = ['r_basicprofile r_emailaddress r_contactinfo'];
    public $responseType = 'json';
    public $authorizationHeader = 'Bearer';
    public $fields = [
        'id', 'email-address', 'first-name', 'last-name', 'headline',
        'location', 'industry', 'picture-url', 'public-profile-url',
    ];

    public function urlAuthorize()
    {
        return 'https://www.linkedin.com/uas/oauth2/authorization';
    }

    public function urlAccessToken()
    {
        return 'https://www.linkedin.com/uas/oauth2/accessToken';
    }

    public function urlUserDetails(AccessToken $token)
    {
        $fields = implode(',', $this->fields);
        return 'https://api.linkedin.com/v1/people/~:(' . $fields . ')?format=json';
    }

    public function userDetails($response, AccessToken $token)
    {
        $user = new User();

        $email = (isset($response->emailAddress)) ? $response->emailAddress : null;
        $location = (isset($response->location->name)) ? $response->location->name : null;
        $description = (isset($response->headline)) ? $response->headline : null;
        $pictureUrl = (isset($response->pictureUrl)) ? $response->pictureUrl : null;

        $user->exchangeArray([
            'uid' => $response->id,
            'name' => $response->firstName.' '.$response->lastName,
            'firstname' => $response->firstName,
            'lastname' => $response->lastName,
            'email' => $email,
            'location' => $location,
            'description' => $description,
            'imageurl' => $pictureUrl,
            'urls' => $response->publicProfileUrl,
        ]);

        return $user;
    }

    public function userUid($response, AccessToken $token)
    {
        return $response->id;
    }

    public function userEmail($response, AccessToken $token)
    {
        return isset($response->emailAddress) && $response->emailAddress
            ? $response->emailAddress
            : null;
    }

    public function userScreenName($response, AccessToken $token)
    {
        return [$response->firstName, $response->lastName];
    }
}
