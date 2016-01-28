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

namespace PHPMailer\PHPMailer\OAuthProvider;

use League\OAuth2\Client\Provider\Google as LeagueGoogle;

/**
 * Wrapper for League Google OAuth2 provider.
 * @package PHPMailer
 * @author @sherryl4george
 * @author Marcus Bointon (@Synchro) <phpmailer@synchromedia.co.uk>
 * @link https://github.com/thephpleague/oauth2-client
 */
class Google extends Base
{
    /**
     * Return the OAuth provider implementation for this adaptor.
     * @return League\OAuth2\Client\Provider\AbstractProvider
     */
    public function getProvider()
    {
        if (is_null($this->provider)) {
            $this->provider = new LeagueGoogle([
                'clientId' => $this->oauthClientId,
                'clientSecret' => $this->oauthClientSecret
            ]);
        }
        return $this->provider;
    }

    public function getOptions()
    {
        return [
            'scope' => ['https://mail.google.com/'],
            'approval_prompt' => 'force'
        ];
    }
}
