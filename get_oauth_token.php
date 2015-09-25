<?php
/**
 * Get an OAuth2 token from Google.
 * * Install this script on your server so that it's accessible
 * as [https/http]://<yourdomain>/<folder>/get_oauth_token.php
 * e.g.: http://localhost/phpmail/get_oauth_token.php
 * * Ensure dependencies are installed with 'composer install'
 * * Set up an app in your Google/Yahoo/Microsoft
 * * Set the script address as the app's redirect URL
 * If no refresh token is obtained when running this file, revoke access to your app
 * using link: https://accounts.google.com/b/0/IssuedAuthSubTokens and run the script again.
 * This script requires PHP 5.4 or later
 * PHP Version 5.4.
 */
require 'vendor/autoload.php';
require 'class.phpmaileroauthprovider.php';
require 'class.phpmaileroauthgoogle.php';
require 'class.phpmaileroauthyahoo.php';
require 'class.phpmaileroauthmicrosoft.php';

session_start();

if (!isset($_GET['code']) && !isset($_GET['provider_name'])) {
    echo '<html><body><b>Select Provider</b>:<br/>';
    echo "<a href='?provider_name=google'>Google</a><br/>";
    echo "<a href='?provider_name=yahoo'>Yahoo</a><br/>";
    echo "<a href='?provider_name=microsoft'>Microsoft/Outlook/Live</a><br/>";
    echo '</body>';
    exit;
}

$provider_name = '';
$options = array();

if (isset($_GET['provider_name'])) {
    $provider_name             = $_GET['provider_name'];
    $_SESSION['provider_name'] = $provider_name;
} elseif (isset($_SESSION['provider_name'])) {
    $provider_name = $_SESSION['provider_name'];
}
//save in session for subsequent requests.
$redirectUri = 'https://kusuma.com/phpmail/PHPMailer/get_oauth_token.php';

if ($provider_name == 'google') {
    $provider         = new League\OAuth2\Client\Provider\Google(array(
        'clientId' => '{GOOGLE_APP_ID}',
        'clientSecret' => '{GOOGLE_APP_SECRET}',
        'redirectUri' => $redirectUri,
        'accessType' => 'offline'
    ));
    //scope for mail
    $options['scope'] = array(
        'https://mail.google.com/'
    );
    
    //To get the refresh token everytime
    $options['approval_prompt'] = 'force';
} elseif ($provider_name == 'yahoo') {
    $provider = new Hayageek\OAuth2\Client\Provider\Yahoo(array(
        'clientId' => '{YAHOO_APP_ID}',
        'clientSecret' => '{YAHOO_APP_SECRET}',
        'redirectUri' => $redirectUri //for yahoo redirect URL should be https
    ));
} elseif ($provider_name == 'microsoft') {
    $provider = new Stevenmaguire\OAuth2\Client\Provider\Microsoft(array(
        'clientId' => '{MICROSOFT_APP_ID}',
        'clientSecret' => '{MICROSOFT_APP_SECRET}',
        'redirectUri' => $redirectUri
    ));
    
    //scopes
    $options['scope'] = array(
        'wl.imap',
        'wl.offline_access'
    );
} else {
    echo 'Not supported for now';
    exit;
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
    unset($_SESSION['provider_name']);
    exit('Invalid state');
} else {
    unset($_SESSION['provider_name']);
    
    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', array(
        'code' => $_GET['code']
    ));
    
    // Use this to interact with an API on the users behalf
    echo 'Token: ' . $token->getToken() . '<br>';
    
    // Use this to get a new access token if the old one expires
    echo 'Refresh Token: ' . $token->getRefreshToken() . '<br>';
    
    // Number of seconds until the access token will expire, and need refreshing
    echo 'Expires:' . $token->getExpires() . '<br>';
}