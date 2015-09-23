<?php
/**
 * PHPMailer - PHP email transport unit tests
 * Requires PHPUnit 3.3 or later.
 *
 * PHP version 5.0.0
 *
 * @package PHPMailer
 * @author Andy Prevost
 * @author Marcus Bointon <phpmailer@synchromedia.co.uk>
 * @copyright 2004 - 2009 Andy Prevost
 * @copyright 2010 Marcus Bointon
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

require_once '../PHPMailerAutoload.php';
require_once '../class.phpmaileroauthprovider.php';

/**
 * PHPMailer - PHP email transport unit test class
 * Performs authentication tests
 */
class PHPMailerXOAuthTest extends PHPUnit_Framework_TestCase
{
    /**
     * Holds the default phpmailer instance.
     * @private
     * @type PHPMailer
     */
    public $Mail;

    /**
     * Holds the SMTP mail host.
     * @public
     * @type string
     */
    public $Host = '';

    /**
     * Default include path
     * @type string
     */
    public $INCLUDE_DIR = '../';

    /**
     * PIDs of any processes we need to kill
     * @type array
     * @access private
     */
    private $pids = array();

    /**
     * Run before each test is started.
     */
    public function setUp()
    {
        if (file_exists('./testbootstrap.php')) {
            include './testbootstrap.php'; //Overrides go in here
        }
        $this->Mail = new PHPMailerOAuth;
        $this->Mail->SMTPDebug = 3; //Full debug output
        $this->Mail->Priority = 3;
        $this->Mail->Encoding = '8bit';
        $this->Mail->CharSet = 'iso-8859-1';
        $this->Mail->From = 'unit_test@phpmailer.example.com';
        $this->Mail->FromName = 'Unit Tester';
        $this->Mail->Sender = '';
        $this->Mail->Subject = 'Unit Test';
        $this->Mail->Body = '';
        $this->Mail->AltBody = '';
        $this->Mail->WordWrap = 0;
        $this->Mail->Port = 587;


        $this->Mail->Helo = 'localhost.localdomain';
        $this->Mail->Username = '';
        $this->Mail->Password = '';
        $this->Mail->PluginDir = $this->INCLUDE_DIR;
        $this->Mail->addReplyTo('no_reply@phpmailer.example.com', 'Reply Guy');
        $this->Mail->Sender = 'unit_test@phpmailer.example.com';
        
        $this->Mail->SMTPSecure = 'tls';
        $this->Mail->SMTPAuth = true;
        $this->Mail->AuthType = 'XOAUTH2';

        $this->Mail->oauthUserEmail = "oauth-user@example.com";
        $this->Mail->oauthClientId = "client_id";
        $this->Mail->oauthClientSecret = "secret_id";
        $this->Mail->oauthRefreshToken = "outh_refresh_token";


        
    }

    /**
     * Run after each test is completed.
     */
    public function tearDown()
    {
        // Clean global variables
        $this->Mail = null;

        foreach ($this->pids as $pid) {
            $p = escapeshellarg($pid);
            shell_exec("ps $p && kill -TERM $p");
        }
    }

     /**
     * Reflection method to test protected methods
     */
   protected static function getMethod($name) 
   {
     $class = new ReflectionClass('PHPMailerOAuthProvider');
     $method = $class->getMethod($name);
     $method->setAccessible(true);
     return $method;
   }


    /**
     * Tests Google XOAuth
     */
    public function testGoogleXOAuth()
    {
        //Set Provider to Google
        $this->Mail->oauthProviderName = PHPMailerOAuthProvider::GOOGLE;

        $oauth = $this->Mail->getOAUTHInstance();
        $this->assertAttributeSame('oauth-user@example.com', 'oauthUserEmail', $oauth);
        $this->assertAttributeSame('client_id', 'oauthClientId', $oauth);
        $this->assertAttributeSame('secret_id', 'oauthClientSecret', $oauth);
        $this->assertAttributeSame('outh_refresh_token', 'oauthRefreshToken', $oauth);
         
        $method = self::getMethod('getProvider');
        $provider = $method->invokeArgs($oauth,array());
        $this->assertInstanceOf('League\OAuth2\Client\Provider\Google', $provider);
         
          
        
    }
        /**
     * Tests Yahoo XOAuth
     */
    public function testYahooXOAuth()
    {
        //Set Provider to Google
        $this->Mail->oauthProviderName = PHPMailerOAuthProvider::YAHOO;

        $oauth = $this->Mail->getOAUTHInstance();
        $this->assertAttributeSame('oauth-user@example.com', 'oauthUserEmail', $oauth);
        $this->assertAttributeSame('client_id', 'oauthClientId', $oauth);
        $this->assertAttributeSame('secret_id', 'oauthClientSecret', $oauth);
        $this->assertAttributeSame('outh_refresh_token', 'oauthRefreshToken', $oauth);
         
        $method = self::getMethod('getProvider');
        $provider = $method->invokeArgs($oauth,array());
        $this->assertInstanceOf('League\OAuth2\Client\Provider\Yahoo', $provider);

  
    }
    /**
     * Tests Microsoft XOAuth
     */
    public function testMicrosoftXOAuth()
    {
        //Set Provider to Google
        $this->Mail->oauthProviderName = PHPMailerOAuthProvider::MICROSOFT;

        $oauth = $this->Mail->getOAUTHInstance();
        $this->assertAttributeSame('oauth-user@example.com', 'oauthUserEmail', $oauth);
        $this->assertAttributeSame('client_id', 'oauthClientId', $oauth);
        $this->assertAttributeSame('secret_id', 'oauthClientSecret', $oauth);
        $this->assertAttributeSame('outh_refresh_token', 'oauthRefreshToken', $oauth);
         
        $method = self::getMethod('getProvider');
        $provider = $method->invokeArgs($oauth,array());
        $this->assertInstanceOf('Stevenmaguire\OAuth2\Client\Provider\Microsoft', $provider);
  
    }

}
