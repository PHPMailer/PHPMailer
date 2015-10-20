<?php
/**
 * PHPMailer - PHP email creation and transport class.
 * PHP Version 5.4.
 *
 * @link https://github.com/PHPMailer/PHPMailer/ The PHPMailer GitHub project
 *
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
 * PHPMailerOAuthProvider class
 * An abstract base class for service-provider-specific OAuth implementations.
 * @author @hayageek
 * @author Ravishanker Kusuma (hayageek@gmail.com)
 */
abstract class PHPMailerOAuthProvider
{
    protected $oauthToken = null;
    
    public function __construct(
        $UserEmail = '',
        $ClientSecret = '',
        $ClientId = '',
        $RefreshToken = ''
    ) {
        $this->oauthClientId = $ClientId;
        $this->oauthClientSecret = $ClientSecret;
        $this->oauthRefreshToken = $RefreshToken;
        $this->oauthUserEmail = $UserEmail;
    }

    abstract public function getProvider();

    public function getOAUTHInstance()
    {
        return $this;
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
        // Get the new token only if it not available or expired
        if ($this->oauthToken == null || ($this->oauthToken != null && $this->oauthToken->hasExpired()))
        {
            $this->oauthToken = $this->getToken();
        }
        return base64_encode('user='.$this->oauthUserEmail."\001auth=Bearer ".$this->oauthToken."\001\001");
    }
}
