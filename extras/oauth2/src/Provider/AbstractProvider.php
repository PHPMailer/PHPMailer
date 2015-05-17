<?php

namespace League\OAuth2\Client\Provider;

use Closure;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Service\Client as GuzzleClient;
use League\OAuth2\Client\Exception\IDPException as IDPException;
use League\OAuth2\Client\Grant\GrantInterface;
use League\OAuth2\Client\Token\AccessToken as AccessToken;

abstract class AbstractProvider implements ProviderInterface
{
    public $clientId = '';

    public $clientSecret = '';

    public $redirectUri = '';

    public $state;

    public $name;

    public $uidKey = 'uid';

    public $scopes = [];

    public $method = 'post';

    public $scopeSeparator = ',';

    public $responseType = 'json';

    public $headers = [];

    public $authorizationHeader;

    /**
     * @var GuzzleClient
     */
    protected $httpClient;

    protected $redirectHandler;

    /**
     * @var int This represents: PHP_QUERY_RFC1738, which is the default value for php 5.4
     *          and the default encoding type for the http_build_query setup
     */
    protected $httpBuildEncType = 1;

    public function __construct($options = [])
    {
        foreach ($options as $option => $value) {
            if (property_exists($this, $option)) {
                $this->{$option} = $value;
            }
        }

        $this->setHttpClient(new GuzzleClient());
    }

    public function setHttpClient(GuzzleClient $client)
    {
        $this->httpClient = $client;

        return $this;
    }

    public function getHttpClient()
    {
        $client = clone $this->httpClient;

        return $client;
    }

    /**
     * Get the URL that this provider uses to begin authorization.
     *
     * @return string
     */
    abstract public function urlAuthorize();

    /**
     * Get the URL that this provider uses to request an access token.
     *
     * @return string
     */
    abstract public function urlAccessToken();

    /**
     * Get the URL that this provider uses to request user details.
     *
     * Since this URL is typically an authorized route, most providers will require you to pass the access_token as
     * a parameter to the request. For example, the google url is:
     *
     * 'https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token='.$token
     *
     * @param AccessToken $token
     * @return string
     */
    abstract public function urlUserDetails(AccessToken $token);

    /**
     * Given an object response from the server, process the user details into a format expected by the user
     * of the client.
     *
     * @param object $response
     * @param AccessToken $token
     * @return mixed
     */
    abstract public function userDetails($response, AccessToken $token);

    public function getScopes()
    {
        return $this->scopes;
    }

    public function setScopes(array $scopes)
    {
        $this->scopes = $scopes;
    }

    public function getAuthorizationUrl($options = [])
    {
        $this->state = isset($options['state']) ? $options['state'] : md5(uniqid(rand(), true));

        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'state' => $this->state,
            'scope' => is_array($this->scopes) ? implode($this->scopeSeparator, $this->scopes) : $this->scopes,
            'response_type' => isset($options['response_type']) ? $options['response_type'] : 'code',
            'approval_prompt' => isset($options['approval_prompt']) ? $options['approval_prompt'] : 'auto',
        ];

        return $this->urlAuthorize().'?'.$this->httpBuildQuery($params, '', '&');
    }

    // @codeCoverageIgnoreStart
    public function authorize($options = [])
    {
        $url = $this->getAuthorizationUrl($options);
        if ($this->redirectHandler) {
            $handler = $this->redirectHandler;
            return $handler($url);
        }
        // @codeCoverageIgnoreStart
        header('Location: ' . $url);
        exit;
        // @codeCoverageIgnoreEnd
    }

    public function getAccessToken($grant = 'authorization_code', $params = [])
    {
        if (is_string($grant)) {
            // PascalCase the grant. E.g: 'authorization_code' becomes 'AuthorizationCode'
            $className = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $grant)));
            $grant = 'League\\OAuth2\\Client\\Grant\\'.$className;
            if (! class_exists($grant)) {
                throw new \InvalidArgumentException('Unknown grant "'.$grant.'"');
            }
            $grant = new $grant();
        } elseif (! $grant instanceof GrantInterface) {
            $message = get_class($grant).' is not an instance of League\OAuth2\Client\Grant\GrantInterface';
            throw new \InvalidArgumentException($message);
        }

        $defaultParams = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
            'grant_type'    => $grant,
        ];

        $requestParams = $grant->prepRequestParams($defaultParams, $params);

        try {
            switch (strtoupper($this->method)) {
                case 'GET':
                    // @codeCoverageIgnoreStart
                    // No providers included with this library use get but 3rd parties may
                    $client = $this->getHttpClient();
                    $client->setBaseUrl($this->urlAccessToken() . '?' . $this->httpBuildQuery($requestParams, '', '&'));
                    $request = $client->get(null, $this->getHeaders(), $requestParams)->send();
                    $response = $request->getBody();
                    break;
                    // @codeCoverageIgnoreEnd
                case 'POST':
                    $client = $this->getHttpClient();
                    $client->setBaseUrl($this->urlAccessToken());
                    $request = $client->post(null, $this->getHeaders(), $requestParams)->send();
                    $response = $request->getBody();
                    break;
                // @codeCoverageIgnoreStart
                default:
                    throw new \InvalidArgumentException('Neither GET nor POST is specified for request');
                // @codeCoverageIgnoreEnd
            }
        } catch (BadResponseException $e) {
            // @codeCoverageIgnoreStart
            $response = $e->getResponse()->getBody();
            // @codeCoverageIgnoreEnd
        }

        $result = $this->prepareResponse($response);

        if (isset($result['error']) && ! empty($result['error'])) {
            // @codeCoverageIgnoreStart
            throw new IDPException($result);
            // @codeCoverageIgnoreEnd
        }

        $result = $this->prepareAccessTokenResult($result);

        return $grant->handleResponse($result);
    }

    /**
     * Prepare the response, parsing according to configuration and returning
     * the response as an array.
     *
     * @param  string $response
     * @return array
     */
    protected function prepareResponse($response)
    {
        $result = [];

        switch ($this->responseType) {
            case 'json':
                $json = json_decode($response, true);

                if (JSON_ERROR_NONE === json_last_error()) {
                    $result = $json;
                }

                break;
            case 'string':
                parse_str($response, $result);
                break;
        }

        return $result;
    }

    /**
     * Prepare the access token response for the grant. Custom mapping of
     * expirations, etc should be done here.
     *
     * @param  array $result
     * @return array
     */
    protected function prepareAccessTokenResult(array $result)
    {
        $this->setResultUid($result);
        return $result;
    }

    /**
     * Sets any result keys we've received matching our provider-defined uidKey to the key "uid".
     *
     * @param array $result
     */
    protected function setResultUid(array &$result)
    {
        // If we're operating with the default uidKey there's nothing to do.
        if ($this->uidKey === "uid") {
            return;
        }

        if (isset($result[$this->uidKey])) {
            // The AccessToken expects a "uid" to have the key "uid".
            $result['uid'] = $result[$this->uidKey];
        }
    }

    public function getUserDetails(AccessToken $token)
    {
        $response = $this->fetchUserDetails($token);

        return $this->userDetails(json_decode($response), $token);
    }

    public function getUserUid(AccessToken $token)
    {
        $response = $this->fetchUserDetails($token, true);

        return $this->userUid(json_decode($response), $token);
    }

    public function getUserEmail(AccessToken $token)
    {
        $response = $this->fetchUserDetails($token, true);

        return $this->userEmail(json_decode($response), $token);
    }

    public function getUserScreenName(AccessToken $token)
    {
        $response = $this->fetchUserDetails($token, true);

        return $this->userScreenName(json_decode($response), $token);
    }

    public function userUid($response, AccessToken $token)
    {
        return isset($response->id) && $response->id ? $response->id : null;
    }

    public function userEmail($response, AccessToken $token)
    {
        return isset($response->email) && $response->email ? $response->email : null;
    }

    public function userScreenName($response, AccessToken $token)
    {
        return isset($response->name) && $response->name ? $response->name : null;
    }

    /**
     * Build HTTP the HTTP query, handling PHP version control options
     *
     * @param  array        $params
     * @param  integer      $numeric_prefix
     * @param  string       $arg_separator
     * @param  null|integer $enc_type
     *
     * @return string
     * @codeCoverageIgnoreStart
     */
    protected function httpBuildQuery($params, $numeric_prefix = 0, $arg_separator = '&', $enc_type = null)
    {
        if (version_compare(PHP_VERSION, '5.4.0', '>=') && !defined('HHVM_VERSION')) {
            if ($enc_type === null) {
                $enc_type = $this->httpBuildEncType;
            }
            $url = http_build_query($params, $numeric_prefix, $arg_separator, $enc_type);
        } else {
            $url = http_build_query($params, $numeric_prefix, $arg_separator);
        }

        return $url;
    }

    protected function fetchUserDetails(AccessToken $token)
    {
        $url = $this->urlUserDetails($token);

        $headers = $this->getHeaders($token);

        return $this->fetchProviderData($url, $headers);
    }

    protected function fetchProviderData($url, array $headers = [])
    {
        try {
            $client = $this->getHttpClient();
            $client->setBaseUrl($url);

            if ($headers) {
                $client->setDefaultOption('headers', $headers);
            }

            $request = $client->get()->send();
            $response = $request->getBody();
        } catch (BadResponseException $e) {
            // @codeCoverageIgnoreStart
            $response = $e->getResponse()->getBody();
            $result = $this->prepareResponse($response);
            throw new IDPException($result);
            // @codeCoverageIgnoreEnd
        }

        return $response;
    }

    protected function getAuthorizationHeaders($token)
    {
        $headers = [];
        if ($this->authorizationHeader) {
            $headers['Authorization'] = $this->authorizationHeader . ' ' . $token;
        }
        return $headers;
    }

    public function getHeaders($token = null)
    {
        $headers = $this->headers;
        if ($token) {
            $headers = array_merge($headers, $this->getAuthorizationHeaders($token));
        }
        return $headers;
    }

    public function setRedirectHandler(Closure $handler)
    {
        $this->redirectHandler = $handler;
    }
}
