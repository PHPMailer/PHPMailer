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

namespace PHPMailer\Test\OAuth;

use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\PHPMailer;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Test OAuth functionality.
 */
final class OAuthTest extends TestCase
{

    /**
     * Test OAuth method
     */
    public function testOAuth()
    {
        $PHPMailer = new PHPMailer();
        $reflection = new \ReflectionClass($PHPMailer);
        $property = $reflection->getProperty('oauth');
        $property->setAccessible(true);
        $property->setValue($PHPMailer, true);
        self::assertTrue($PHPMailer->getOAuth());

        $options = [
            'provider' => 'dummyprovider',
            'userName' => 'dummyusername',
            'clientSecret' => 'dummyclientsecret',
            'clientId' => 'dummyclientid',
            'refreshToken' => 'dummyrefreshtoken',
        ];

        $oauth = new OAuth($options);
        self::assertInstanceOf(OAuth::class, $oauth);
        $subject = $PHPMailer->setOAuth($oauth);
        self::assertNull($subject);
        self::assertInstanceOf(OAuth::class, $PHPMailer->getOAuth());
    }
}
