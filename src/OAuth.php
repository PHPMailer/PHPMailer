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

namespace PHPMailer\PHPMailer;

class OAuth {

    /**
     * @var League\OAuth2\Client\Provider\AbstractProvider
     */
    protected $provider = null;

    /**
     * @var League\OAuth2\Client\Token\AccessToken
     */
    protected $oauthToken = null;

    /**
     * @var string
     */
    protected $oauthUserEmail = '';

    /**
     * @var string
     */
    protected $oauthClientSecret = '';

    /**
     * @var string
     */
    protected $oauthClientId = '';

    /**
     * @var string
     */
    protected $refreshToken = '';

    public function __construct(
    $options) {
        $this->provider = $options['provider'];
        $this->oauthUserEmail = $options['userName'];
        $this->oauthClientSecret = $options['clientSecret'];
        $this->oauthClientId = $options['clientId'];
        $this->oauthRefreshToken = $options['refreshToken'];
    }

    /**
     * @return League\OAuth2\Client\Provider\AbstractProvider
     */
//    abstract public function getProvider();

    /**
     * Returns the current value of the state parameter.
     *
     * This can be accessed by the redirect handler during authorization.
     *
     * @return string
     */
    public function getState() {
        return $this->state;
    }

    /**
     * @return \League\OAuth2\Client\Grant\RefreshToken
     */
    protected function getGrant() {
        return new \League\OAuth2\Client\Grant\RefreshToken();
    }

    /**
     * @return League\OAuth2\Client\Token\AccessToken
     */
    protected function getToken() {
        $provider = $this->provider;
        $grant = $this->getGrant();
        return $provider->getAccessToken($grant, ['refresh_token' => $this->oauthRefreshToken]);
    }

    /**
     * Generate a base64-encoded OAuth token.
     * @return string
     */
    public function getOauth64() {
        // Get a new token if it's not available or has expired
        if (is_null($this->oauthToken) or $this->oauthToken->hasExpired()) {
            $this->oauthToken = $this->getToken();
        }
        return base64_encode('user=' . $this->oauthUserEmail . "\001auth=Bearer " . $this->oauthToken . "\001\001");
    }

}
