<?php

/**
 * PHPMailer - PHP email transport unit tests.
 * PHP version 5.5.
 *
 * @author    Marcus Bointon <phpmailer@synchromedia.co.uk>
 * @author    Andy Prevost
 * @copyright 2012 - 2020 Marcus Bointon
 * @copyright 2004 - 2009 Andy Prevost
 * @license   https://www.gnu.org/licenses/old-licenses/lgpl-2.1.html GNU Lesser General Public License
 */

namespace PHPMailer\Test\OAuth;

use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\OAuthTokenProvider;
use PHPMailer\PHPMailer\PHPMailer;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Test OAuth functionality.
 */
final class OAuthTest extends TestCase
{
    /**
     * Test OAuth class.
     *
     * @covers PHPMailer\PHPMailer\PHPMailer::getOAuth
     * @covers PHPMailer\PHPMailer\PHPMailer::setOAuth
     * @covers PHPMailer\PHPMailer\OAuth::__construct
     * @covers PHPMailer\PHPMailer\OAuthTokenProvider
     */
    public function testOAuth()
    {
        $PHPMailer = new PHPMailer(true);
        $reflection = new \ReflectionClass($PHPMailer);
        $property = $reflection->getProperty('oauth');
        (\PHP_VERSION_ID < 80100) && $property->setAccessible(true);
        $property->setValue($PHPMailer, true);
        self::assertTrue($PHPMailer->getOAuth(), 'Initial value of oauth property is not true');

        $options = [
            'provider' => 'dummyprovider',
            'userName' => 'dummyusername',
            'clientSecret' => 'dummyclientsecret',
            'clientId' => 'dummyclientid',
            'refreshToken' => 'dummyrefreshtoken',
        ];

        $oauth = new OAuth($options);
        self::assertInstanceOf(OAuth::class, $oauth, 'Instantiation of OAuth class failed');
        self::assertInstanceOf(OAuthTokenProvider::class, $oauth, 'Instantiation of OAuth class failed');
        $subject = $PHPMailer->setOAuth($oauth);
        self::assertNull($subject, 'setOAuth() is not a void function');
        self::assertInstanceOf(
            OAuth::class,
            $PHPMailer->getOAuth(),
            'Setting OAuth property to an instance of the OAuth class failed'
        );
        $PHPMailer->setOAuth(new DummyOAuthProvider());
        self::assertInstanceOf(
            OAuthTokenProvider::class,
            $PHPMailer->getOAuth(),
            'Setting OAuth property to an instance of the OAuth class failed (2)'
        );
    }

    /**
     * A refresh response that carries no new refresh token must not discard the current one.
     *
     * @covers PHPMailer\PHPMailer\OAuth::getOauth64
     * @covers PHPMailer\PHPMailer\OAuth::getRefreshToken
     */
    public function testRefreshTokenKeptWhenNotReissued()
    {
        $provider = new DummyTokenProvider();
        $oauth = new DummyGrantOAuth([
            'provider' => $provider,
            'userName' => 'dummyusername',
            'clientSecret' => 'dummyclientsecret',
            'clientId' => 'dummyclientid',
            'refreshToken' => 'dummyrefreshtoken',
        ]);

        $oauth->getOauth64();
        self::assertSame('dummyrefreshtoken', $oauth->getRefreshToken());

        //The access token has expired, so this requests another one with the same refresh token
        $oauth->getOauth64();
        self::assertSame(['dummyrefreshtoken', 'dummyrefreshtoken'], $provider->refreshTokens);

        $provider->newRefreshToken = 'rotatedrefreshtoken';
        $oauth->getOauth64();
        self::assertSame('rotatedrefreshtoken', $oauth->getRefreshToken());
    }
}

class DummyGrantOAuth extends OAuth
{
    protected function getGrant()
    {
        return 'refresh_token';
    }
}

class DummyTokenProvider
{
    public $refreshTokens = [];
    public $newRefreshToken = null;

    public function getAccessToken($grant, array $options)
    {
        $this->refreshTokens[] = $options['refresh_token'];

        return new DummyAccessToken($this->newRefreshToken);
    }
}

class DummyAccessToken
{
    private $refreshToken;

    public function __construct($refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    public function hasExpired()
    {
        return true;
    }

    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    public function __toString()
    {
        return 'dummyaccesstoken';
    }
}

class DummyOauthProvider implements OAuthTokenProvider
{
    public function getOauth64()
    {
        return 'oauth';
    }
}
