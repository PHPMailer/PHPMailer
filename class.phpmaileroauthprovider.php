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
 * @copyright 2012 - 2014 Marcus Bointon
 * @copyright 2010 - 2012 Jim Jagielski
 * @copyright 2004 - 2009 Andy Prevost
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * PHPMailerOAuthProvider - Wrapper for League OAuth2 Google/Yahoo/Microsoft provider.
 * @package PHPMailer
 * @author @hayageek
 * @author Ravishanker Kusuma (hayageek@gmail.com)
 * @link https://github.com/hayageek
 */
class PHPMailerOAuthProvider
{
	const GOOGLE = 'google';
	const YAHOO = 'yahoo';
	const MICROSOFT = 'microsoft';
	

    private $oauthUserEmail = '';
    private $oauthRefreshToken = '';
    private $oauthClientId = '';
    private $oauthClientSecret = '';
    private $oauthProviderName = '';

    public function __construct($OAuthProvider,
        $UserEmail,
        $ClientSecret,
        $ClientId,
        $RefreshToken
    ) {
    	$this->oauthProviderName = $OAuthProvider;
        $this->oauthClientId = $ClientId;
        $this->oauthClientSecret = $ClientSecret;
        $this->oauthRefreshToken = $RefreshToken;
        $this->oauthUserEmail = $UserEmail;
    }

    private function getProvider() {
    
    	if($this->oauthProviderName == self::GOOGLE)
    	{
	        return new League\OAuth2\Client\Provider\Google([
    	        'clientId' => $this->oauthClientId,
        	    'clientSecret' => $this->oauthClientSecret
	        ]);
        }
        else if($this->oauthProviderName == self::YAHOO)
        {
        	 return new League\OAuth2\Client\Provider\Yahoo([
    	        'clientId' => $this->oauthClientId,
        	    'clientSecret' => $this->oauthClientSecret
	        ]);
        }
        else if($this->oauthProviderName == self::MICROSOFT)
        {
        	 return new Stevenmaguire\OAuth2\Client\Provider\Microsoft([
    	        'clientId' => $this->oauthClientId,
        	    'clientSecret' => $this->oauthClientSecret

	        ]);
        }
        
    }

    private function getGrant()
    {
        return new \League\OAuth2\Client\Grant\RefreshToken();
    }

    private function getToken()
    {
        $provider = $this->getProvider();
        $grant = $this->getGrant();
        return $provider->getAccessToken($grant, ['refresh_token' => $this->oauthRefreshToken]);
    }

    public function getOauth64()
    {
        $token = $this->getToken();
        return base64_encode("user=" . $this->oauthUserEmail . "\001auth=Bearer " . $token . "\001\001");
    }
}
