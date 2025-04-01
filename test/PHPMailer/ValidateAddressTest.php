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

namespace PHPMailer\Test\PHPMailer;

use PHPMailer\PHPMailer\PHPMailer;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Test email address validation.
 *
 * @todo Recommendation JRF: Rework the tests to actually test all test cases
 *       against each type of build-in pattern.
 *       As things stand, only the PHP validation is tested (while it shouldn't be as that's
 *       the responsibility of PHP Core), while the PCRE and HTML5 regexes are untested, while
 *       those are maintained within this repo.
 *       There should also be a test to make sure that `auto` and `noregex` correctly
 *       fall through to the default validation.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::validateAddress
 */
final class ValidateAddressTest extends TestCase
{
    /**
     * Run before this test class.
     */
    public static function set_up_before_class()
    {
        // Make sure that validator property starts off with its default value.
        PHPMailer::$validator = 'php';
    }

    /**
     * Run after this test class.
     */
    public static function tear_down_after_class()
    {
        self::set_up_before_class();
    }

    /**
     * Testing against the pre-defined patterns with a valid address (for coverage).
     *
     * @dataProvider dataPatterns
     *
     * @param string $pattern Validation pattern.
     */
    public function testPatternsValidAddress($pattern)
    {
        self::assertTrue(
            PHPMailer::validateAddress('test@example.com', $pattern),
            'Good address that failed validation against pattern ' . $pattern
        );
    }

    /**
     * Testing against the pre-defined patterns with an invalid address (for coverage).
     *
     * @dataProvider dataPatterns
     *
     * @param string $pattern Validation pattern.
     */
    public function testPatternsInvalidAddress($pattern)
    {
        self::assertFalse(
            PHPMailer::validateAddress('test@example.com.', $pattern),
            'Bad address that passed validation against pattern ' . $pattern
        );
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataPatterns()
    {
        $patterns = [
            'auto',
            'pcre',
            'pcre8',
            'html5',
            'php',
            'noregex',
        ];

        return $this->arrayToNamedDataProvider($patterns);
    }

    /**
     * Verify that valid addresses are recognized as such.
     *
     * @dataProvider dataValidAddresses
     * @dataProvider dataAsciiAddresses
     * @dataProvider dataValidIPv6
     *
     * @param string $emailAddress The address to test.
     */
    public function testValidAddresses($emailAddress)
    {
        self::assertTrue(PHPMailer::validateAddress($emailAddress), 'Good address that failed validation');
    }

    /**
     * Data provider for valid addresses.
     *
     * @return array
     */
    public function dataValidAddresses()
    {
        $validaddresses = [
            'first@example.org',
            'first.last@example.org',
            '1234567890123456789012345678901234567890123456789012345678901234@example.org',
            '"first\"last"@example.org',
            '"first@last"@example.org',
            '"first\last"@example.org',
            'first.last@[12.34.56.78]',
            'first.last@x23456789012345678901234567890123456789012345678901234567890123.example.org',
            'first.last@123.example.org',
            '"first\last"@example.org',
            '"Abc\@def"@example.org',
            '"Fred\ Bloggs"@example.org',
            '"Joe.\Blow"@example.org',
            '"Abc@def"@example.org',
            'user+mailbox@example.org',
            'customer/department=shipping@example.org',
            '$A12345@example.org',
            '!def!xyz%abc@example.org',
            '_somename@example.org',
            'dclo@us.example.com',
            'peter.piper@example.org',
            'test@example.org',
            'TEST@example.org',
            '1234567890@example.org',
            'test+test@example.org',
            'test-test@example.org',
            't*est@example.org',
            '+1~1+@example.org',
            '{_test_}@example.org',
            'test.test@example.org',
            '"test.test"@example.org',
            'test."test"@example.org',
            '"test@test"@example.org',
            'test@123.123.123.x123',
            'test@[123.123.123.123]',
            'test@example.example.org',
            'test@example.example.example.org',
            '"test\test"@example.org',
            '"test\blah"@example.org',
            '"test\blah"@example.org',
            '"test\"blah"@example.org',
            'customer/department@example.org',
            '_Yosemite.Sam@example.org',
            '~@example.org',
            '"Austin@Powers"@example.org',
            'Ima.Fool@example.org',
            '"Ima.Fool"@example.org',
            '"first"."last"@example.org',
            '"first".middle."last"@example.org',
            '"first".last@example.org',
            'first."last"@example.org',
            '"first"."middle"."last"@example.org',
            '"first.middle"."last"@example.org',
            '"first.middle.last"@example.org',
            '"first..last"@example.org',
            '"first\"last"@example.org',
            'first."mid\dle"."last"@example.org',
            'name.lastname@example.com',
            'a@example.com',
            'aaa@[123.123.123.123]',
            'a-b@example.com',
            '+@b.c',
            '+@b.com',
            'a@b.co-foo.uk',
            'valid@about.museum',
            'shaitan@my-domain.thisisminekthx',
            '"Joe\Blow"@example.org',
            'user%uucp!path@example.edu',
            'cdburgess+!#$%&\'*-/=?+_{}|~test@example.com',
            'test@test.com',
            'test@xn--example.com',
            'test@example.com',
        ];

        return $this->arrayToNamedDataProvider($validaddresses, 'Valid: ');
    }

    /**
     * Data provider for IDNs in ASCII form.
     *
     * @return array
     */
    public function dataAsciiAddresses()
    {
        $asciiaddresses = [
            'first.last@xn--bcher-kva.ch',
            'first.last@xn--j1ail.xn--p1ai',
            'first.last@xn--phplst-6va.com',
        ];

        return $this->arrayToNamedDataProvider($asciiaddresses, 'Valid ascii: ');
    }

    /**
     * Data provider for valid explicit IPv6 numeric addresses.
     *
     * @todo Fix the failing (commented out) tests.
     *
     * @return array
     */
    public function dataValidIPv6()
    {
        $validipv6 = [
            //'first.last@[IPv6:::a2:a3:a4:b1:b2:b3:b4]',
            //'first.last@[IPv6:a1:a2:a3:a4:b1:b2:b3::]',
            'first.last@[IPv6:::]',
            'first.last@[IPv6:::b4]',
            'first.last@[IPv6:::b3:b4]',
            'first.last@[IPv6:a1::b4]',
            'first.last@[IPv6:a1::]',
            'first.last@[IPv6:a1:a2::]',
            'first.last@[IPv6:0123:4567:89ab:cdef::]',
            'first.last@[IPv6:0123:4567:89ab:CDEF::]',
            'first.last@[IPv6:::a3:a4:b1:ffff:11.22.33.44]',
            //'first.last@[IPv6:::a2:a3:a4:b1:ffff:11.22.33.44]',
            'first.last@[IPv6:a1:a2:a3:a4::11.22.33.44]',
            //'first.last@[IPv6:a1:a2:a3:a4:b1::11.22.33.44]',
            'first.last@[IPv6:a1::11.22.33.44]',
            'first.last@[IPv6:a1:a2::11.22.33.44]',
            'first.last@[IPv6:0123:4567:89ab:cdef::11.22.33.44]',
            'first.last@[IPv6:0123:4567:89ab:CDEF::11.22.33.44]',
            'first.last@[IPv6:a1::b2:11.22.33.44]',
            'first.last@[IPv6:::12.34.56.78]',
            'first.last@[IPv6:1111:2222:3333::4444:12.34.56.78]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666:12.34.56.78]',
            'first.last@[IPv6:::1111:2222:3333:4444:5555:6666]',
            'first.last@[IPv6:1111:2222:3333::4444:5555:6666]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666::]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666:7777:8888]',
            //'first.last@[IPv6:1111:2222:3333::4444:5555:12.34.56.78]',
            //'first.last@[IPv6:1111:2222:3333::4444:5555:6666:7777]',
        ];

        return $this->arrayToNamedDataProvider($validipv6, 'Valid IPv6: ');
    }

    /**
     * Verify that invalid addresses are recognized as such.
     *
     * @dataProvider dataInvalidAddresses
     * @dataProvider dataUnicodeAddresses
     * @dataProvider dataInvalidPHPPattern
     *
     * @param string $emailAddress The address to test.
     */
    public function testInvalidAddresses($emailAddress)
    {
        self::assertFalse(PHPMailer::validateAddress($emailAddress), 'Bad address that passed validation');
    }

    /**
     * Data provider for invalid addresses.
     *
     * Some failing cases commented out that are apparently up for debate!
     *
     * @return array
     */
    public function dataInvalidAddresses()
    {
        $invalidaddresses = [
            'first.last@sub.do,com',
            'first\@last@iana.org',
            '123456789012345678901234567890123456789012345678901234567890' .
            '@12345678901234567890123456789012345678901234 [...]',
            'first.last',
            '12345678901234567890123456789012345678901234567890123456789012345@iana.org',
            '.first.last@iana.org',
            'first.last.@iana.org',
            'first..last@iana.org',
            '"first"last"@iana.org',
            '"""@iana.org',
            '"\"@iana.org',
            //'""@iana.org',
            'first\@last@iana.org',
            'first.last@',
            'x@x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.' .
            'x23456789.x23456789.x23456789.x23 [...]',
            'first.last@[.12.34.56.78]',
            'first.last@[12.34.56.789]',
            'first.last@[::12.34.56.78]',
            'first.last@[IPv5:::12.34.56.78]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:12.34.56.78]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666:7777:12.34.56.78]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666:7777]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666:7777:8888:9999]',
            'first.last@[IPv6:1111:2222::3333::4444:5555:6666]',
            'first.last@[IPv6:1111:2222:333x::4444:5555]',
            'first.last@[IPv6:1111:2222:33333::4444:5555]',
            'first.last@-xample.com',
            'first.last@exampl-.com',
            'first.last@x234567890123456789012345678901234567890123456789012345678901234.iana.org',
            'abc\@def@iana.org',
            'abc\@iana.org',
            'Doug\ \"Ace\"\ Lovell@iana.org',
            'abc@def@iana.org',
            'abc\@def@iana.org',
            'abc\@iana.org',
            '@iana.org',
            'doug@',
            '"qu@iana.org',
            'ote"@iana.org',
            '.dot@iana.org',
            'dot.@iana.org',
            'two..dot@iana.org',
            '"Doug "Ace" L."@iana.org',
            'Doug\ \"Ace\"\ L\.@iana.org',
            'hello world@iana.org',
            //'helloworld@iana .org',
            'gatsby@f.sc.ot.t.f.i.tzg.era.l.d.',
            'test.iana.org',
            'test.@iana.org',
            'test..test@iana.org',
            '.test@iana.org',
            'test@test@iana.org',
            'test@@iana.org',
            '-- test --@iana.org',
            '[test]@iana.org',
            '"test"test"@iana.org',
            '()[]\;:,><@iana.org',
            'test@.',
            'test@example.',
            'test@.org',
            'test@12345678901234567890123456789012345678901234567890123456789012345678901234567890' .
            '12345678901234567890 [...]',
            'test@[123.123.123.123',
            'test@123.123.123.123]',
            'NotAnEmail',
            '@NotAnEmail',
            '"test"blah"@iana.org',
            '.wooly@iana.org',
            'wo..oly@iana.org',
            'pootietang.@iana.org',
            '.@iana.org',
            'Ima Fool@iana.org',
            'phil.h\@\@ck@haacked.com',
            'foo@[\1.2.3.4]',
            //'first."".last@iana.org',
            'first\last@iana.org',
            'Abc\@def@iana.org',
            'Fred\ Bloggs@iana.org',
            'Joe.\Blow@iana.org',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666:12.34.567.89]',
            '{^c\@**Dog^}@cartoon.com',
            //'"foo"(yay)@(hoopla)[1.2.3.4]',
            'cal(foo(bar)@iamcal.com',
            'cal(foo)bar)@iamcal.com',
            'cal(foo\)@iamcal.com',
            'first(12345678901234567890123456789012345678901234567890)last@(1234567890123456789' .
            '01234567890123456789012 [...]',
            'first(middle)last@iana.org',
            'first(abc("def".ghi).mno)middle(abc("def".ghi).mno).last@(abc("def".ghi).mno)example' .
            '(abc("def".ghi).mno). [...]',
            'a(a(b(c)d(e(f))g)(h(i)j)@iana.org',
            '.@',
            '@bar.com',
            '@@bar.com',
            'aaa.com',
            'aaa@.com',
            'aaa@.123',
            'aaa@[123.123.123.123]a',
            'aaa@[123.123.123.333]',
            'a@bar.com.',
            'a@-b.com',
            'a@b-.com',
            '-@..com',
            '-@a..com',
            'invalid@about.museum-',
            'test@...........com',
            '"Unicode NULL' . chr(0) . '"@char.com',
            'Unicode NULL' . chr(0) . '@char.com',
            'first.last@[IPv6::]',
            'first.last@[IPv6::::]',
            'first.last@[IPv6::b4]',
            'first.last@[IPv6::::b4]',
            'first.last@[IPv6::b3:b4]',
            'first.last@[IPv6::::b3:b4]',
            'first.last@[IPv6:a1:::b4]',
            'first.last@[IPv6:a1:]',
            'first.last@[IPv6:a1:::]',
            'first.last@[IPv6:a1:a2:]',
            'first.last@[IPv6:a1:a2:::]',
            'first.last@[IPv6::11.22.33.44]',
            'first.last@[IPv6::::11.22.33.44]',
            'first.last@[IPv6:a1:11.22.33.44]',
            'first.last@[IPv6:a1:::11.22.33.44]',
            'first.last@[IPv6:a1:a2:::11.22.33.44]',
            'first.last@[IPv6:0123:4567:89ab:cdef::11.22.33.xx]',
            'first.last@[IPv6:0123:4567:89ab:CDEFF::11.22.33.44]',
            'first.last@[IPv6:a1::a4:b1::b4:11.22.33.44]',
            'first.last@[IPv6:a1::11.22.33]',
            'first.last@[IPv6:a1::11.22.33.44.55]',
            'first.last@[IPv6:a1::b211.22.33.44]',
            'first.last@[IPv6:a1::b2::11.22.33.44]',
            'first.last@[IPv6:a1::b3:]',
            'first.last@[IPv6::a2::b4]',
            'first.last@[IPv6:a1:a2:a3:a4:b1:b2:b3:]',
            'first.last@[IPv6::a2:a3:a4:b1:b2:b3:b4]',
            'first.last@[IPv6:a1:a2:a3:a4::b1:b2:b3:b4]',
            //This is a valid RFC5322 address, but we don't want to allow it for obvious reasons!
            "(\r\n RCPT TO:user@example.com\r\n DATA \\\nSubject: spam10\\\n\r\n Hello," .
            "\r\n this is a spam mail.\\\n.\r\n QUIT\r\n ) a@example.net",
        ];

        return $this->arrayToNamedDataProvider($invalidaddresses, 'Invalid: ');
    }

    /**
     * Data provider for IDNs in Unicode form.
     *
     * @return array
     */
    public function dataUnicodeAddresses()
    {
        $unicodeaddresses = [
            'first.last@bücher.ch',
            'first.last@кто.рф',
            'first.last@phplíst.com',
        ];

        return $this->arrayToNamedDataProvider($unicodeaddresses, 'Invalid Unicode: ');
    }

    /**
     * Data provider.
     *
     * These are invalid according to PHP's filter_var() email filter,
     * which doesn't allow dotless domains, numeric TLDs or unbracketed IPv4 literals.
     *
     * @return array
     */
    public function dataInvalidPHPPattern()
    {
        $invalidphp = [
            'a@b',
            'a@bar',
            'first.last@com',
            'test@123.123.123.123',
            'foobar@192.168.0.1',
            'first.last@example.123',
        ];

        return $this->arrayToNamedDataProvider($invalidphp, 'Invalid PHP: ');
    }

    /**
     * Create a dataprovider array from a single-dimensional array.
     *
     * Each item will have it's value as the test case name for easier debugging.
     *
     * @param array  $items  Single dimensional array.
     * @param string $prefix Optional. Prefix to add to the data set name.
     *
     * @return array
     */
    protected function arrayToNamedDataProvider($items, $prefix = '')
    {
        $provider = [];
        foreach ($items as $item) {
            $provider[$prefix . $item] = [$item];
        }

        return $provider;
    }
}
