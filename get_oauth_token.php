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

namespace PHPMailer\PHPMailer;

// Give alias to the League Provider Classes that may be used
use League\OAuth2\Client\Provider\Google as Google;
use Stevenmaguire\OAuth2\Client\Provider\Microsoft as Microsoft;
use Hayageek\OAuth2\Client\Provider\Yahoo as Yahoo;

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

    require 'vendor/autoload.php';

    session_start();

    $providerName = '';

    if (array_key_exists('provider', $_GET)) {
        $providerName = $_GET['provider'];
        $_SESSION['provider'] = $providerName;
    } elseif (array_key_exists('provider', $_SESSION)) {
        $providerName = $_SESSION['provider'];
    }
    if (!in_array($providerName, ['Google', 'Microsoft', 'Yahoo'])) {
        exit("Only Google, Microsoft and Yahoo OAuth2 providers are currently supported.");
    }
    
    //These details obtained are by setting up app in Google developer console.
    // Changed the hardcode to variables above, so even newbies can use it easily.
    $clientId = 'RANDOMCHARS-----duv1n2.apps.googleusercontent.com';
    $clientSecret = 'RANDOMCHARS-----lGyjPcRtvP';

    //If this automatic URL doesn't work, set it yourself manually
    $redirectUri = isset($_SERVER['HTTPS']) ? 'https://' : 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
    //$redirectUri = 'http://localhost/PHPMailer/redirect';

    $provider = new Google(
            [
        'clientId' => $clientId,
        'clientSecret' => $clientSecret,
        'redirectUri' => $redirectUri,
        'accessType' => 'offline']
    );

    // Define scopes as a array here.
    $options = ['scope' => [
            'https://mail.google.com/'
    ]];


    if (!isset($_GET['code'])) {

        // If we don't have an authorization code then get one
        $authUrl = $provider->getAuthorizationUrl($options);

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
        // Use this to get a new access token if the old one expires
        echo 'Refresh Token: ' . $token->getRefreshToken();
    }
    ?>
