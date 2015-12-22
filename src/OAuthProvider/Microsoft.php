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

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

/**
 * Wrapper for League Microsoft OAuth2 provider.
 * @package PHPMailer
 * @author @hayageek
 * @author @sherryl4george
 * @link https://github.com/sherryl4george
 */

class Microsoft extends Base
{
    public function getProvider()
    {
        if (is_null($this->provider)) {
            $this->provider = new Microsoft([
                'clientId' => $this->oauthClientId,
                'clientSecret' => $this->oauthClientSecret
            ]);
        }
        return $this->provider;
    }
		
	/**
     * @param array $options
     * @return string
	 * All Options that are to be passed to the Google Server can be set here
     */
    public function getOptions()
    {
        return [
            'scope' => ['wl.imap',
						'wl.offline_access']            
        ];
    }
	
	public function getBaseAuthorizationUrl()
    {		
        return 'https://login.live.com/oauth20_authorize.srf';
    }
	
	/**
     * @param array $options
     * @return string
     */

    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://login.live.com/oauth20_token.srf';
    }

    protected function getAuthorizationParameters(array $options)
    {
		$tmp_options = $this->getOptions();
		if (is_array($tmp_options['scope'])) {		
            $separator = $this->getScopeSeparator();
            $tmp_options['scope'] = implode($separator, $tmp_options['scope']);
        }
		
        $params = array_merge(
            parent::getAuthorizationParameters($options),
            array_filter($tmp_options)
        );      
        return $params;
    }
	
	/**
     * Check a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @return void
     */
    protected function checkResponseUtility(ResponseInterface $response, $data)
    {
        if (isset($data['error'])) {
			var_dump($data);
			//exit();
            throw new IdentityProviderException(
                (isset($data['error']['message']) ? $data['error']['message'] : $response->getReasonPhrase()),
                $response->getStatusCode(),
                $response
            );
        }
    }

    protected function getDefaultScopes()
    {
       return [
           'wl.imap',
           'wl.offline_access'           
        ];		
    }

    protected function getScopeSeparator()
    {
        return ' ';
    }       
}
