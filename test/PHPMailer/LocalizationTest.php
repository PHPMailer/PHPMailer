<?php

/**
 * PHPMailer - PHP email transport unit tests.
 * PHP version 5.5.
 *
 * @author    Marcus Bointon <phpmailer@synchromedia.co.uk>
 * @author    Andy Prevost
 * @copyright 2012 - 2020 Marcus Bointon
 * @copyright 2004 - 2009 Andy Prevost
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace PHPMailer\Test\PHPMailer;

use ReflectionMethod;
use PHPMailer\Test\TestCase;

/**
 * Test localized error message functionality.
 *
 * {@internal In a number of tests unassigned language codes are being used
 * on purpose so as not to conflict with translations which will potentially be
 * added in the future.
 * If at any point in the future, these code would be _assigned_, the tests should
 * be updated to use some other, as yet unassigned language code.}
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::getTranslations
 * @covers \PHPMailer\PHPMailer\PHPMailer::lang
 * @covers \PHPMailer\PHPMailer\PHPMailer::setLanguage
 */
final class LocalizationTest extends TestCase
{
    /**
     * Test setting the preferred language for error messages.
     *
     * @dataProvider dataSetLanguageSuccess
     *
     * @param string $phrase   The "empty_message" phrase in the expected language for verification.
     * @param string $langCode Optional. Language code.
     * @param string $langPath Optional. Path to the language file directory.
     */
    public function testSetLanguageSuccess($phrase, $langCode = null, $langPath = null)
    {
        if (isset($langCode, $langPath)) {
            $result = $this->Mail->setLanguage($langCode, $langPath);
        } elseif (isset($langCode)) {
            $result = $this->Mail->setLanguage($langCode);
        } else {
            $result = $this->Mail->setLanguage();
        }

        $lang = $this->Mail->getTranslations();

        self::assertTrue(
            $result,
            'Setting the language failed. Translations set to: ' . var_export($lang, true)
        );

        self::assertIsArray($lang, 'Translations is not an array');
        self::assertArrayHasKey('empty_message', $lang, 'The "empty_message" key is unavailable');
        self::assertSame($phrase, $lang['empty_message'], 'The "empty_message" translation is not as expected');
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataSetLanguageSuccess()
    {
        $customPath = dirname(__DIR__) . '/Fixtures/LocalizationTest/';

        return [
            'Default language (en)' => [
                'phrase'   => 'Message body empty',
            ],
            'Renamed language (dk => da)' => [
                'phrase'   => 'Meddelelsen er uden indhold',
                'langCode' => 'dk',
            ],
            'Available language: "nl"' => [
                'phrase'   => 'Berichttekst is leeg',
                'langCode' => 'nl',
            ],
            'Available language: "NL" (uppercase)' => [
                'phrase'   => 'Berichttekst is leeg',
                'langCode' => 'NL',
            ],
            'Available language: "pt_br"' => [
                'phrase'   => 'Mensagem vazia',
                'langCode' => 'pt_br',
            ],
            'Available language: "Zh_Cn" (mixed case)' => [
                'phrase'   => '邮件正文为空。',
                'langCode' => 'Zh_Cn',
            ],
            'Available language: "sr_latn"' => [
                'phrase'   => 'Sadržaj poruke je prazan.',
                'langCode' => 'sr_latn',
            ],
            'Custom path: available language with code in "lang-script-country" format' => [
                'phrase'   => 'XA Lang-script-country file found',
                'langCode' => 'xa_scri_cc',
                'langPath' => $customPath,
            ],
            'Custom path: available language: "nl" (single quoted translation text)' => [
                'phrase'   => 'Custom path test success (nl)',
                'langCode' => 'nl',
                'langPath' => $customPath,
            ],
            'Custom path: available language: "fr" (double quoted translation text)' => [
                'phrase'   => 'Custom path test success (fr)',
                'langCode' => 'fr',
                'langPath' => $customPath,
            ],
        ];
    }

    /**
     * Test the fall-back logic for when a more specific language code is passed, for which a translation
     * doesn't exist, while a translation for a related ("parent") language code does exist.
     *
     * {@internal This test re-uses the logic of the success test, but having it as a separate test
     * allows for the test to report under its own name, making the test results more descriptive.}
     *
     * @dataProvider dataSetLanguageSuccessFallBackLogic
     *
     * @param string $phrase   The "empty_message" phrase in the expected language for verification.
     * @param string $langCode Optional. Language code.
     * @param string $langPath Optional. Path to the language file directory.
     */
    public function testSetLanguageSuccessFallBackLogic($phrase, $langCode = null, $langPath = null)
    {
        $this->testSetLanguageSuccess($phrase, $langCode, $langPath);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataSetLanguageSuccessFallBackLogic()
    {
        $customPath = dirname(__DIR__) . '/Fixtures/LocalizationTest/';

        return [
            'Request: "lang-script-country" (not available), receive "lang-script"' => [
                'phrase'   => 'XB Lang-script file found',
                'langCode' => 'xb_scri_cc',
                'langPath' => $customPath,
            ],
            'Request: "lang-script-country" (not available), receive "lang-country"' => [
                'phrase'   => 'XC Lang-country file found',
                'langCode' => 'xc_scri_cc',
                'langPath' => $customPath,
            ],
            'Request: "lang-script-country" (not available), receive "lang-country" (prefer country over script)' => [
                'phrase'   => 'XD Lang-country file found',
                'langCode' => 'xd_scri_cc',
                'langPath' => $customPath,
            ],
            'Request: "lang-script-country" (not available), receive "lang" (no country or script available)' => [
                'phrase'   => 'XE Lang file found',
                'langCode' => 'xe_scri_cc',
                'langPath' => $customPath,
            ],

            'Request: "lang-script" (not available), receive "lang" even when "lang_country" exists' => [
                'phrase'   => '郵件內容為空',
                'langCode' => 'zh_Hant',
            ],
            'Request: "lang-script" (not available), receive "lang" (no country or script available)' => [
                'phrase'   => 'Corps du message vide.',
                'langCode' => 'fr_latn',
            ],

            'Request: "lang-country" (not available), receive "lang" even when "lang_script" exists' => [
                'phrase'   => 'Садржај поруке је празан.',
                'langCode' => 'sr_rs',
            ],
            'Request: "lang-country" (not available), receive "lang" (no country or script available)' => [
                'phrase'   => 'Berichttekst is leeg',
                'langCode' => 'nl_NL',
            ],
        ];
    }

    /**
     * Test that setting the preferred language for error messages fails when the language file
     * could not be found/is inaccessible.
     *
     * @dataProvider dataSetLanguageFail
     *
     * @param string $langCode Optional. Language code.
     * @param string $langPath Optional. Path to the language file directory.
     */
    public function testSetLanguageFail($langCode = null, $langPath = null)
    {
        if (isset($langCode, $langPath)) {
            $result = $this->Mail->setLanguage($langCode, $langPath);
        } elseif (isset($langCode)) {
            $result = $this->Mail->setLanguage($langCode);
        } else {
            $result = $this->Mail->setLanguage();
        }

        $lang = $this->Mail->getTranslations();

        self::assertFalse(
            $result,
            'Setting the language did not fail. Translations set to: ' . var_export($lang, true)
        );

        // Verify that the translations have still be set (in English).
        self::assertIsArray($lang, 'Translations is not an array');
        self::assertArrayHasKey('empty_message', $lang, 'The "empty_message" key is unavailable');
        self::assertSame(
            'Message body empty',
            $lang['empty_message'],
            'The "empty_message" translation is not as expected'
        );
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataSetLanguageFail()
    {
        $customPath = dirname(__DIR__) . '/Fixtures/LocalizationTest/';

        return [
            'Unavailable language (Quechuan), fall back to default (en)' => [
                'langCode' => 'qu',
            ],
            'Unavailable Language-country code (is_IS), unavailable lang code (is), fall back to default (en)' => [
                'langCode' => 'is_IS',
            ],
            'Unavailable Language-script code (pa_Arab), unavailable lang code (pa), fall back to default (en)' => [
                'langCode' => 'pa_Arab',
            ],
            'Invalid lang-country-script order (sr_rs_latin), fall back to default (en)' => [
                'phrase'   => 'Садржај поруке је празан.',
                'langCode' => 'sr_rs_latin',
            ],
            'Available language-country code, but using dash (pt-br): fall back to default (en)' => [
                'langCode' => 'pt-br',
            ],
            /*
             * Note: The first two letters of this three letter language code should match an existing
             * language file for this test to test this properly.
             */
            'Invalid language code (ISO 639-2 "hrv"): fallback to default (en)' => [
                'phrase'   => 'Message body empty',
                'langCode' => 'hrv',
            ],
            'Custom path: unavailable language (Quechuan)' => [
                'langCode' => 'qu',
                'langPath' => $customPath,
            ],
            'Custom path: not a local/permitted path' => [
                'langCode' => 'xx', // Unassigned lang code.
                'langPath' => 'http://example.com/files/',
            ],
            'Custom path: path traversal' => [
                'langCode' => 'xx', // Unassigned lang code.
                'langPath' => './../../composer.json?',
            ],
            'Custom path: missing trailing slash, file exists but should not be loaded' => [
                'langCode' => 'xx', // Unassigned lang code.
                'langPath' => dirname(__DIR__) . '/Fixtures/LocalizationTest',
            ],
        ];
    }

    /**
     * Test that arbitrary code in a language file does not get executed.
     */
    public function testSetLanguageDoesNotExecuteCodeInLangFile()
    {
        $result = $this->Mail->setLanguage(
            'yy', // Unassigned lang code.
            dirname(__DIR__) . '/Fixtures/LocalizationTest/'
        );
        $lang   = $this->Mail->getTranslations();

        self::assertTrue($result, 'Setting the language failed. Translations set to: ' . var_export($lang, true));
        self::assertIsArray($lang, 'Translations is not an array');

        // Verify that the fixture file was loaded.
        self::assertArrayHasKey('extension_missing', $lang, 'The "extension_missing" translation key was not found');
        self::assertSame(
            'Confirming that test fixture was loaded correctly (yy).',
            $lang['extension_missing'],
            'The "extension_missing" translation is not as expected'
        );

        // Verify that arbitrary code in a translation file does not get processed.
        self::assertArrayHasKey('empty_message', $lang, 'The "empty_message" translation key was not found');
        self::assertSame(
            'Message body empty',
            $lang['empty_message'],
            'The "empty_message" translation is not as expected'
        );

        self::assertArrayHasKey('encoding', $lang, 'The "encoding" translation key was not found');
        self::assertSame(
            'Unknown encoding: ',
            $lang['encoding'],
            'The "encoding" translation is not as expected'
        );

        self::assertArrayHasKey('execute', $lang, 'The "execute" translation key was not found');
        self::assertSame(
            'Could not execute: ',
            $lang['execute'],
            'The "execute" translation is not as expected'
        );

        self::assertArrayHasKey('signing', $lang, 'The "signing" translation key was not found');
        self::assertSame(
            'Double quoted but not interpolated $composer',
            $lang['signing'],
            'The "signing" translation is not as expected'
        );
    }

    /**
     * Test that text strings passed in from a language file for arbitrary keys do not get processed.
     */
    public function testSetLanguageOnlyProcessesKnownKeys()
    {
        $result = $this->Mail->setLanguage(
            'zz', // Unassigned lang code.
            dirname(__DIR__) . '/Fixtures/LocalizationTest/'
        );
        $lang   = $this->Mail->getTranslations();

        self::assertTrue($result, 'Setting the language failed. Translations set to: ' . var_export($lang, true));
        self::assertIsArray($lang, 'Translations is not an array');

        // Verify that the fixture file was loaded.
        self::assertArrayHasKey('extension_missing', $lang, 'The "extension_missing" translation key was not found');
        self::assertSame(
            'Confirming that test fixture was loaded correctly (zz).',
            $lang['extension_missing'],
            'The "extension_missing" translation is not as expected'
        );

        // Verify that unknown translation keys do not get processed.
        self::assertArrayNotHasKey('unknown', $lang, 'The "unknown" key was found');
        self::assertNotContains(
            'Unknown text.',
            $lang,
            'The text for the "unknown" key was found in the array'
        );

        self::assertArrayNotHasKey('invalid', $lang, 'The "invalid" key was found');
        self::assertNotContains(
            'Invalid text.',
            $lang,
            'The text for the "invalid" key was found in the array'
        );

        // Verify that known translation keys which do not match the expected case do not get processed.
        self::assertNotContains(
            'Overruled text, index not same case',
            $lang,
            'Non-exact key matches were processed anyway'
        );
    }

    /**
     * Test retrieving the applicable language strings.
     *
     * @dataProvider dataGetTranslations
     *
     * @param string $langCode Optional. The language to set.
     */
    public function testGetTranslations($langCode = null)
    {
        if (isset($langCode)) {
            $this->Mail->setLanguage($langCode);
        }

        $result = $this->Mail->getTranslations();
        self::assertIsArray($result);
        self::assertNotCount(0, $result);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataGetTranslations()
    {
        return [
            'No explicit language set' => [
               'langCode' => null,
            ],
            'Language explicitly set' => [
               'langCode' => 'es',
            ],
        ];
    }

    /**
     * Test retrieving a - potentially localized - text string.
     *
     * @dataProvider dataLang
     *
     * @param string $input    Text string identifier key.
     * @param string $expected Expected function return value.
     * @param string $langCode Optional. The language to retrieve the text in.
     */
    public function testLang($input, $expected, $langCode = null)
    {
        if (isset($langCode)) {
            $this->Mail->setLanguage($langCode);
        }

        $reflMethod = new ReflectionMethod($this->Mail, 'lang');
        $reflMethod->setAccessible(true);
        $result = $reflMethod->invoke($this->Mail, $input);
        $reflMethod->setAccessible(false);

        self::assertSame($expected, $result);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataLang()
    {
        return [
            'Key: "invalid_address", default language (en)' => [
               'input'    => 'invalid_address',
               'expected' => 'Invalid address: ',
            ],
            'Key: "provide_address", explicit language: en' => [
               'input'    => 'provide_address',
               'expected' => 'You must provide at least one recipient email address.',
               'langCode' => 'en',
            ],
            'Key: "encoding", explicit language: nl' => [
               'input'    => 'encoding',
               'expected' => 'Onbekende codering: ',
               'langCode' => 'nl',
            ],
            'Key: "mailer_not_supported", explicit language: ja' => [
               'input'    => 'mailer_not_supported',
               'expected' => ' メーラーがサポートされていません。',
               'langCode' => 'ja',
            ],
            'Key: "smtp_connect_failed", default language (en)' => [
               'input'    => 'smtp_connect_failed',
               'expected' => 'SMTP connect() failed. https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting',
            ],
            'Key: "smtp_connect_failed", explicit language: es' => [
               'input'    => 'smtp_connect_failed',
               'expected' => 'SMTP Connect() falló. https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting',
               'langCode' => 'es',
            ],
            'Non-existent key returns key, default language (en)' => [
               'input'    => 'notasupportedkey',
               'expected' => 'notasupportedkey',
            ],
            'Non-existent key returns key, explicit language: es' => [
               'input'    => 'notasupportedkey',
               'expected' => 'notasupportedkey',
               'langCode' => 'es',
            ],
        ];
    }
}
