<?php

/**
 * PHPMailer - Base test class.
 * PHP version 5.5.
 *
 * @author    Marcus Bointon <phpmailer@synchromedia.co.uk>
 * @author    Andy Prevost
 * @copyright 2012 - 2020 Marcus Bointon
 * @copyright 2004 - 2009 Andy Prevost
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace PHPMailer\Test;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use ReflectionClass;
use ReflectionProperty;
use Yoast\PHPUnitPolyfills\TestCases\TestCase as PolyfillTestCase;

/**
 * PHPMailer - Base test class.
 */
abstract class TestCase extends PolyfillTestCase
{
    /**
     * Whether or not to initialize the PHPMailer object to throw exceptions.
     *
     * Overload this constant in a concrete test class and set the value to `true`
     * to initialize PHPMailer with Exceptions turned on.
     *
     * @var bool|null
     */
    const USE_EXCEPTIONS = null;

    /**
     * Property names and their values for the test instance of the PHPMailer class.
     *
     * These (public) properties will be set in the `set_up()` method.
     *
     * This property can be enhanced/overloaded in concrete test classes to change the presets
     * or add additional properties.
     *
     * It is the responsibility of the individual test classes to ensure that
     * property values of the correct type are passed.
     *
     * @var array Key is the property name, value the desired value for the PHPMailer instance.
     */
    protected $propertyChanges = [
        'SMTPDebug'   => SMTP::DEBUG_CONNECTION, // Full debug output.
        'Debugoutput' => ['PHPMailer\Test\DebugLogTestListener', 'debugLog'],
    ];

    /**
     * Holds the PHPMailer instance.
     *
     * @var PHPMailer
     */
    protected $Mail;

    /**
     * Holds the change log.
     *
     * @var string[]
     */
    private $ChangeLog = [];

    /**
     * Holds the note log.
     *
     * @var string[]
     */
    private $NoteLog = [];

    /*
     * List of *public* properties which we don't want listed in the changelog
     * as they will already be included in the mail/debug information
     * created in `buildBody()` anyway.
     *
     * Note: no need to include protected or private properties as the tests don't
     * have access to those anyway.
     *
     * @var array Key is the property name, value irrelevant.
     */
    private $changelogExclude = [
        // These are always set in set_up().
        'SMTPDebug'   => true,
        'Debugoutput' => true,

        // These are part of the message body anyway.
        'Subject'     => true,
        'Body'        => true,
        'AltBody'     => true,
        'Ical'        => true,

        // These will always change.
        'MessageID'   => true,
        'MessageDate' => true,

        // These are always explicitly added via buildBody() anyway.
        'ContentType' => true,
        'CharSet'     => true,
        'Host'        => true,
    ];

    /**
     * List of *static* properties in the PHPMailer class which _may_ be changed from within a test,
     * with their default values.
     *
     * This list is used by the {@see `TestCase::resetStaticProperties()`} method, as well as
     * in the {@see `TestCase::checkChanges()`} method.
     *
     * {@internal The default values have to be (manually) maintained here as the Reflection
     * extension does not provide accurate information on the default values of static properties.}
     *
     * @var array Key is the property name, value the default as per the PHPMailer class.
     */
    private $PHPMailerStaticProps = [
        'LE'        => PHPMailer::CRLF,
        'validator' => 'php',
    ];

    /**
     * Run before each test class.
     */
    public static function set_up_before_class()
    {
        if (defined('PHPMAILER_INCLUDE_DIR') === false) {
            /*
             * Set up default include path.
             * Default to the dir above the test dir, i.e. the project home dir.
             */
            define('PHPMAILER_INCLUDE_DIR', dirname(__DIR__));
        }
    }

    /**
     * Run before each test is started.
     */
    protected function set_up()
    {
        // Initialize the PHPMailer class.
        if (is_bool(static::USE_EXCEPTIONS)) {
            $this->Mail = new PHPMailer(static::USE_EXCEPTIONS);
        } else {
            $this->Mail = new PHPMailer();
        }

        // Set initial property values.
        foreach ($this->propertyChanges as $key => $value) {
            if ($key === 'to' || $key === 'cc' || $key === 'bcc' || $key === 'ReplyTo') {
                if (is_array($value) && isset($value['address'], $value['name'])) {
                    $this->setAddress($value['address'], $value['name'], $key);
                } elseif (is_string($value)) {
                    $this->setAddress($value, '', $key);
                }

                continue;
            }

            $this->Mail->{$key} = $value;
        }

        if ($this->Mail->Host != '') {
            $this->Mail->isSMTP();
        } else {
            $this->Mail->isMail();
        }
    }

    /**
     * Run after each test is completed.
     */
    protected function tear_down()
    {
        // Make sure that any changes to static variables are undone after each test.
        $this->resetStaticProperties();

        // Clean test class native properties between tests.
        $this->Mail = null;
        $this->ChangeLog = [];
        $this->NoteLog = [];
    }

    /**
     * Reset the static properties in the PHPMailer class to their default values.
     */
    protected function resetStaticProperties()
    {
        $reflClass        = new ReflectionClass(PHPMailer::class);
        $staticPropValues = $reflClass->getStaticProperties();

        foreach ($this->PHPMailerStaticProps as $name => $default) {
            if (isset($staticPropValues[$name]) && $staticPropValues[$name] === $default) {
                continue;
            }

            self::updateStaticProperty(PHPMailer::class, $name, $default);
        }
    }

    /**
     * Update the value of a - potentially inaccessible - static property in a class.
     *
     * @param string $className    The target class.
     * @param string $propertyName The name of the static property.
     * @param mixed  $value        The new value for the property.
     */
    public static function updateStaticProperty($className, $propertyName, $value)
    {
        $reflProp = new ReflectionProperty($className, $propertyName);
        if (PHP_VERSION_ID < 80100) {
            //setAccessible is only needed in PHP < 8.1
            $isPublic = $reflProp->isPublic();
            if ($isPublic !== true) {
                $reflProp->setAccessible(true);
            }
        }

        $reflProp->setValue(null, $value);

        if (PHP_VERSION_ID < 80100) {
            if ($isPublic !== true) {
                $reflProp->setAccessible(false);
            }
        }
    }

    /**
     * Build the body of the message in the appropriate format.
     */
    protected function buildBody()
    {
        $this->checkChanges();

        // Determine line endings for message.
        if ('text/html' === $this->Mail->ContentType || $this->Mail->AltBody !== '') {
            $eol = "<br>\r\n";
            $bullet_start = '<li>';
            $bullet_end = "</li>\r\n";
            $list_start = "<ul>\r\n";
            $list_end = "</ul>\r\n";
        } else {
            $eol = "\r\n";
            $bullet_start = ' - ';
            $bullet_end = "\r\n";
            $list_start = '';
            $list_end = '';
        }

        $ReportBody = '';

        $ReportBody .= '---------------------' . $eol;
        $ReportBody .= 'Unit Test Information' . $eol;
        $ReportBody .= '---------------------' . $eol;
        $ReportBody .= 'phpmailer version: ' . PHPMailer::VERSION . $eol;
        $ReportBody .= 'Content Type: ' . $this->Mail->ContentType . $eol;
        $ReportBody .= 'CharSet: ' . $this->Mail->CharSet . $eol;

        if ($this->Mail->Host !== '') {
            $ReportBody .= 'Host: ' . $this->Mail->Host . $eol;
        }

        // If attachments then create an attachment list.
        $attachments = $this->Mail->getAttachments();
        if (count($attachments) > 0) {
            $ReportBody .= 'Attachments:' . $eol;
            $ReportBody .= $list_start;
            foreach ($attachments as $attachment) {
                $ReportBody .= $bullet_start . 'Name: ' . $attachment[1] . ', ';
                $ReportBody .= 'Encoding: ' . $attachment[3] . ', ';
                $ReportBody .= 'Type: ' . $attachment[4] . $bullet_end;
            }
            $ReportBody .= $list_end . $eol;
        }

        // If there are changes then list them.
        if (count($this->ChangeLog) > 0) {
            $ReportBody .= 'Changes' . $eol;
            $ReportBody .= '-------' . $eol;

            $ReportBody .= $list_start;
            foreach ($this->ChangeLog as $iValue) {
                $ReportBody .= $bullet_start . $iValue[0] . ' was changed to [' .
                    $iValue[1] . ']' . $bullet_end;
            }
            $ReportBody .= $list_end . $eol . $eol;
        }

        // If there are notes then list them.
        if (count($this->NoteLog) > 0) {
            $ReportBody .= 'Notes' . $eol;
            $ReportBody .= '-----' . $eol;

            $ReportBody .= $list_start;
            foreach ($this->NoteLog as $iValue) {
                $ReportBody .= $bullet_start . $iValue . $bullet_end;
            }
            $ReportBody .= $list_end;
        }

        // Re-attach the original body.
        $this->Mail->Body .= $eol . $ReportBody;
    }

    /**
     * Check which default settings have been changed for the report.
     */
    protected function checkChanges()
    {
        // Get the default values of all public properties.
        $defaults = get_class_vars(PHPMailer::class);

        foreach ($defaults as $propertyName => $value) {
            if (isset($this->changelogExclude[$propertyName])) {
                continue;
            }

            if (isset($this->PHPMailerStaticProps[$propertyName])) {
                // Nested static access is not supported in PHP < 7.0, so we need an interim variable.
                $mail = $this->Mail;
                if ($mail::${$propertyName} !== $this->PHPMailerStaticProps[$propertyName]) {
                    $this->addChange($propertyName, var_export($mail::${$propertyName}, true));
                }

                continue;
            }

            // Check against the TestCase specific defaults.
            if (
                isset($this->propertyChanges[$propertyName])
                && $this->Mail->{$propertyName} !== $this->propertyChanges[$propertyName]
            ) {
                $this->addChange($propertyName, var_export($this->Mail->{$propertyName}, true));
                continue;
            }

            // Check against the PHPMailer class defaults.
            if ($this->Mail->{$propertyName} !== $value) {
                $this->addChange($propertyName, var_export($this->Mail->{$propertyName}, true));
            }
        }
    }

    /**
     * Add a changelog entry.
     *
     * @param string $sName
     * @param string $sNewValue
     */
    protected function addChange($sName, $sNewValue)
    {
        $this->ChangeLog[] = [$sName, $sNewValue];
    }

    /**
     * Adds a simple note to the message.
     *
     * @param string $sValue
     */
    protected function addNote($sValue)
    {
        $this->NoteLog[] = $sValue;
    }

    /**
     * Adds all of the addresses.
     *
     * @param string $sAddress
     * @param string $sName
     * @param string $sType
     *
     * @return bool
     */
    protected function setAddress($sAddress, $sName = '', $sType = 'to')
    {
        switch ($sType) {
            case 'to':
                return $this->Mail->addAddress($sAddress, $sName);
            case 'cc':
                return $this->Mail->addCC($sAddress, $sName);
            case 'bcc':
                return $this->Mail->addBCC($sAddress, $sName);
            case 'ReplyTo':
                return $this->Mail->addReplyTo($sAddress, $sName);
        }

        return false;
    }
}
