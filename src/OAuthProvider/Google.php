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

namespace PHPMailer\PHPMailer\OAuthProvider;

/**
 * PHPMailer Google OAuth class- Wrapper for League OAuth2 Google provider.
 * @package PHPMailer
 * @author @sherryl4george
 * @author Marcus Bointon (@Synchro) <phpmailer@synchromedia.co.uk>
 * @link https://github.com/thephpleague/oauth2-client
 */
class PHPMailerOAuthGoogle extends PHPMailerOAuthProvider
class Google
{
    public function getProvider() {
        return new League\OAuth2\Client\Provider\Google([
            'clientId' => $this->oauthClientId,
            'clientSecret' => $this->oauthClientSecret
        ]);
    }
}
