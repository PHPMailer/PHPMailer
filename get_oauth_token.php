<?php
/**
 * PHPMailer - PHP email creation and transport class.
 * PHP Version 5.4
 * @package PHPMailer
 * @link https://github.com/PHPMailer/PHPMailer/ The PHPMailer GitHub project
 * @author Marcus Bointon (Synchro/coolbru) <phpmailer@synchromedia.co.uk>
 * @author Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 * @author Brent R. Matzelle (original founder)
 * @copyright 2012 - 2015 Marcus Bointon
 * @copyright 2010 - 2012 Jim Jagielski
 * @copyright 2004 - 2009 Andy Prevost
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * Get an OAuth2 token from an OAuth2 provider.
 * * Install this script on your server so that it's accessible
 * as [https/http]://<yourdomain>/<folder>/get_oauth_token.php
 * e.g.: http://localhost/phpmailer/get_oauth_token.php
 * * Ensure dependencies are installed with 'composer install'
 * * Set up an app in your Google/Yahoo/Microsoft account
 * * Set the script address as the app's redirect URL
 * If no refresh token is obtained when running this file,
 * revoke access to your app and run the script again.
 */

if (!isset($_GET['code']) && !isset($_GET['provider'])) {
    ?>
    <html>
    <body>Select Provider:<br/>
    <a href='?provider=Google'>Google</a><br/>
    <a href='?provider=Yahoo'>Yahoo</a><br/>
    <a href='?provider=Microsoft'>Microsoft/Outlook/Hotmail/Live/Office365</a><br/>
    </body>
    <?php
    exit;
}

namespace League\OAuth2\Client\Provider;

require 'vendor/autoload.php';

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

session_start();

$providerName = '';

if (array_key_exists('provider', $_GET)) {
    $providerName             = $_GET['provider'];
    $_SESSION['provider'] = $providerName;
} elseif (array_key_exists('provider', $_SESSION)) {
    $providerName = $_SESSION['provider'];
}
if (!preg_match('/^(Google|Microsoft|Yahoo)$/', $providerName)) {
    exit("Only Google, Microsoft and Yahoo OAuth2 providers are currently supported.");
}

//Alter this to point at the URL of this script on your own server
//Should be an HTTPS URL
$redirectUri = 'https://example.com/PHPMailer/get_oauth_token.php';

$providerClass = '\\PHPMailer\\PHPMailer\\OAuthProvider\\'.$providerName;

$provider = new $providerClass(
    '{YOUR_APP_ID}',
    '{YOUR_APP_SECRET}',
    $redirectUri,
    'offline'
);

class Google extends AbstractProvider
{
    use BearerAuthorizationTrait;

    const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'id';

    /**
     * @var string If set, this will be sent to google as the "access_type" parameter.
     * @link https://developers.google.com/accounts/docs/OAuth2WebServer#offline
     */
    protected $accessType;

    /**
     * @var string If set, this will be sent to google as the "hd" parameter.
     * @link https://developers.google.com/accounts/docs/OAuth2Login#hd-param
     */
    protected $hostedDomain;

    /**
     * @var string If set, this will be sent to google as the "scope" parameter.
     * @link https://developers.google.com/gmail/api/auth/scopes
     */
    protected $scope;

    public function getBaseAuthorizationUrl()
    {
        return 'https://accounts.google.com/o/oauth2/auth';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://accounts.google.com/o/oauth2/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return ' ';
    }

    protected function getAuthorizationParameters(array $options)
    {
        if (is_array($this->scope)) {
            $separator = $this->getScopeSeparator();
            $this->scope = implode($separator, $this->scope);
        }

        $params = array_merge(
            parent::getAuthorizationParameters($options),
            array_filter(
                [
                    'hd' => $this->hostedDomain,
                    'access_type' => $this->accessType,
                    'scope' => $this->scope,
                    // if the user is logged in with more than one account ask which one to use for the login!
                    'authuser' => '-1'
                ]
            )
        );
        return $params;
    }

    protected function getDefaultScopes()
    {
        return [
            'email',
            'openid',
            'profile',
        ];
    }

    protected function getScopeSeparator()
    {
        return ' ';
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data['error'])) {
            $code = 0;
            $error = $data['error'];

            if (is_array($error)) {
                $code = $error['code'];
                $error = $error['message'];
            }

            throw new IdentityProviderException($error, $code, $data);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new GoogleUser($response);
    }
}

if (!isset($_GET['code'])) {
    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl($options);

    // echo $authUrl."<br>";
    $_SESSION['oauth2state'] = $provider->getState();

    header('Location: ' . $authUrl);
    exit;
// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    unset($_SESSION['provider']);
    exit('Invalid state');
} else {
    unset($_SESSION['provider']);

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', array(
        'code' => $_GET['code']
    ));

    // Use this to interact with an API on the users behalf
    echo 'Token: ' . $token->getToken() . '<br>';
    $token = $provider->getAccessToken(
        'authorization_code',
        [
            'code' => $_GET['code']
        ]
    );

    // Use this to get a new access token if the old one expires
    echo 'Refresh Token: ' . $token->getRefreshToken() . '<br>';

    // Number of seconds until the access token will expire, and need refreshing
    echo 'Expires:' . $token->getExpires() . '<br>';
}
