<?php
/**
 * PHPMailer - language file tests.
 *
 * PHP version 5.5.
 *
 * @author    Marcus Bointon <phpmailer@synchromedia.co.uk>
 * @author    Andy Prevost
 * @copyright 2010 - 2017 Marcus Bointon
 * @copyright 2004 - 2009 Andy Prevost
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace PHPMailer\Test;

use PHPMailer\PHPMailer\PHPMailer;
use PHPUnit\Framework\TestCase;

/**
 * Check language files for missing or excess translations.
 */
final class PHPMailerLangTest extends TestCase
{
    /**
     * Holds a PHPMailer instance.
     *
     * @var PHPMailer
     */
    private $Mail;

    /**
     * Run before each test is started.
     */
    protected function setUp()
    {
        $this->Mail = new PHPMailer();
    }

    /**
     * Test language files for missing and excess translations.
     * All languages are compared with English.
     *
     * @group languages
     */
    public function testTranslations()
    {
        $this->Mail->setLanguage('en');
        $definedStrings = $this->Mail->getTranslations();
        $err = '';
        foreach (new \DirectoryIterator(__DIR__ . '/../language') as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }
            $matches = [];
            //Only look at language files, ignore anything else in there
            if (preg_match('/^phpmailer\.lang-([a-z_]{2,})\.php$/', $fileInfo->getFilename(), $matches)) {
                $lang = $matches[1]; //Extract language code
                $PHPMAILER_LANG = []; //Language strings get put in here
                include $fileInfo->getPathname(); //Get language strings
                $missing = array_diff(array_keys($definedStrings), array_keys($PHPMAILER_LANG));
                $extra = array_diff(array_keys($PHPMAILER_LANG), array_keys($definedStrings));
                if (!empty($missing)) {
                    $err .= "\nMissing translations in $lang: " . implode(', ', $missing);
                }
                if (!empty($extra)) {
                    $err .= "\nExtra translations in $lang: " . implode(', ', $extra);
                }
            }
        }
        $this->assertEmpty($err, $err);
    }
}
