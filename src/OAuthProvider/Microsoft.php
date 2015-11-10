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

use Stevenmaguire\OAuth2\Client\Provider\Microsoft as StevenmaguireMicrosoft;

/**
 * Wrapper for League Microsoft OAuth2 provider.
 * @package PHPMailer
 * @author @hayageek
 * @author Ravishanker Kusuma (hayageek@gmail.com)
 * @link https://github.com/hayageek
 */

class Microsoft extends Base
{
    public function getProvider()
    {
        if (is_null($this->provider)) {
            $this->provider = new StevenmaguireMicrosoft([
                'clientId' => $this->oauthClientId,
                'clientSecret' => $this->oauthClientSecret
            ]);
        }
        return $this->provider;
    }

    public function getOptions()
    {
        return [
            'scope' => [
                'wl.imap',
                'wl.offline_access'
            ]
        ];
    }
}
