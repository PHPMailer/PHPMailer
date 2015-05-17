<?php
require 'vendor/autoload.php';
session_start();

$provider = new League\OAuth2\Client\Provider\Google ([
    'clientId' => '514660853060-ricm3v62kao2a366rfbii7p05gduv1n2.apps.googleusercontent.com',
    'clientSecret' => '-mxiTaO4UCWufYlGyjPcRtvP',
    'redirectUri' => 'http://localhost/oauth2/'
        ]);

$refreshToken = '1/OW2M-buR4OfDWxgOvPT003r-yFUV49TQYag7_Aod7y0';
$grant = new \League\OAuth2\Client\Grant\RefreshToken();
$token = $provider->getAccessToken($grant, ['refresh_token' => $refreshToken]);
echo $token;

//if (!isset($_GET['code'])) {
//
//    // If we don't have an authorization code then get one
//    $authUrl = $provider->getAuthorizationUrl();
//    $_SESSION['oauth2state'] = $provider->state;
//    header('Location: ' . $authUrl);
//    exit;
//// Check given state against previously stored one to mitigate CSRF attack
//} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
//
//    unset($_SESSION['oauth2state']);
//    exit('Invalid state');
//} else {
//
//    $provider->accessType = 'offline';
//    // Try to get an access token (using the authorization code grant)
//    $token = $provider->getAccessToken('authorization_code', [
//        'code' => $_GET['code']
//    ]);
//
//    // Optional: Now you have a token you can look up a users profile data
////    try {
////
////        // We got an access token, let's now get the user's details
////        $userDetails = $provider->getUserDetails($token);
////
////        // Use these details to create a new profile
////        printf('Hello %s!', $userDetails->firstName);
////    } catch (Exception $e) {
////        echo $e;
////        // Failed to get user details
////        exit('Oh dear...');
////    }
//
//    // Use this to interact with an API on the users behalf
//    echo $token->accessToken.'<br>';
//
//    // Use this to get a new access token if the old one expires
//    echo $token->refreshToken.'<br>';
//
//    // Unix timestamp of when the token will expire, and need refreshing
//    echo $token->expires;
//}
?>