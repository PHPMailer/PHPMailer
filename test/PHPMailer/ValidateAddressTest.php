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

use PHPMailer\PHPMailer\PHPMailer;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Test email address validation.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::validateAddress
 */
final class ValidateAddressTest extends TestCase
{

    /**
     * Test email address validation.
     * Test addresses obtained from http://isemail.info
     * Some failing cases commented out that are apparently up for debate!
     */
    public function testValidate()
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
        //These are invalid according to PHP's filter_var
        //which doesn't allow dotless domains, numeric TLDs or unbracketed IPv4 literals
        $invalidphp = [
            'a@b',
            'a@bar',
            'first.last@com',
            'test@123.123.123.123',
            'foobar@192.168.0.1',
            'first.last@example.123',
        ];
        //Valid RFC 5322 addresses using quoting and comments
        //Note that these are *not* all valid for RFC5321
        $validqandc = [
            'HM2Kinsists@(that comments are allowed)this.is.ok',
            '"Doug \"Ace\" L."@example.org',
            '"[[ test ]]"@example.org',
            '"Ima Fool"@example.org',
            '"test blah"@example.org',
            '(foo)cal(bar)@(baz)example.com(quux)',
            'cal@example(woo).(yay)com',
            'cal(woo(yay)hoopla)@example.com',
            'cal(foo\@bar)@example.com',
            'cal(foo\)bar)@example.com',
            'first().last@example.org',
            'pete(his account)@silly.test(his host)',
            'c@(Chris\'s host.)public.example',
            'jdoe@machine(comment). example',
            '1234 @ local(blah) .machine .example',
            'first(abc.def).last@example.org',
            'first(a"bc.def).last@example.org',
            'first.(")middle.last(")@example.org',
            'first(abc\(def)@example.org',
            'first.last@x(1234567890123456789012345678901234567890123456789012345678901234567890).com',
            'a(a(b(c)d(e(f))g)h(i)j)@example.org',
            '"hello my name is"@example.com',
            '"Test \"Fail\" Ing"@example.org',
            'first.last @example.org',
        ];
        //Valid explicit IPv6 numeric addresses
        $validipv6 = [
            'first.last@[IPv6:::a2:a3:a4:b1:b2:b3:b4]',
            'first.last@[IPv6:a1:a2:a3:a4:b1:b2:b3::]',
            'first.last@[IPv6:::]',
            'first.last@[IPv6:::b4]',
            'first.last@[IPv6:::b3:b4]',
            'first.last@[IPv6:a1::b4]',
            'first.last@[IPv6:a1::]',
            'first.last@[IPv6:a1:a2::]',
            'first.last@[IPv6:0123:4567:89ab:cdef::]',
            'first.last@[IPv6:0123:4567:89ab:CDEF::]',
            'first.last@[IPv6:::a3:a4:b1:ffff:11.22.33.44]',
            'first.last@[IPv6:::a2:a3:a4:b1:ffff:11.22.33.44]',
            'first.last@[IPv6:a1:a2:a3:a4::11.22.33.44]',
            'first.last@[IPv6:a1:a2:a3:a4:b1::11.22.33.44]',
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
            'first.last@[IPv6:1111:2222:3333::4444:5555:12.34.56.78]',
            'first.last@[IPv6:1111:2222:3333::4444:5555:6666:7777]',
        ];
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
        //IDNs in Unicode and ASCII forms.
        $unicodeaddresses = [
            'first.last@bücher.ch',
            'first.last@кто.рф',
            'first.last@phplíst.com',
        ];
        $asciiaddresses = [
            'first.last@xn--bcher-kva.ch',
            'first.last@xn--j1ail.xn--p1ai',
            'first.last@xn--phplst-6va.com',
        ];
        $goodfails = [];
        foreach (array_merge($validaddresses, $asciiaddresses) as $address) {
            if (!PHPMailer::validateAddress($address)) {
                $goodfails[] = $address;
            }
        }
        $badpasses = [];
        foreach (array_merge($invalidaddresses, $unicodeaddresses) as $address) {
            if (PHPMailer::validateAddress($address)) {
                $badpasses[] = $address;
            }
        }
        $err = '';
        if (count($goodfails) > 0) {
            $err .= "Good addresses that failed validation:\n";
            $err .= implode("\n", $goodfails);
        }
        if (count($badpasses) > 0) {
            if (!empty($err)) {
                $err .= "\n\n";
            }
            $err .= "Bad addresses that passed validation:\n";
            $err .= implode("\n", $badpasses);
        }
        self::assertEmpty($err, $err);

        //For coverage
        self::assertTrue(PHPMailer::validateAddress('test@example.com', 'auto'));
        self::assertFalse(PHPMailer::validateAddress('test@example.com.', 'auto'));
        self::assertTrue(PHPMailer::validateAddress('test@example.com', 'pcre'));
        self::assertFalse(PHPMailer::validateAddress('test@example.com.', 'pcre'));
        self::assertTrue(PHPMailer::validateAddress('test@example.com', 'pcre8'));
        self::assertFalse(PHPMailer::validateAddress('test@example.com.', 'pcre8'));
        self::assertTrue(PHPMailer::validateAddress('test@example.com', 'html5'));
        self::assertFalse(PHPMailer::validateAddress('test@example.com.', 'html5'));
        self::assertTrue(PHPMailer::validateAddress('test@example.com', 'php'));
        self::assertFalse(PHPMailer::validateAddress('test@example.com.', 'php'));
        self::assertTrue(PHPMailer::validateAddress('test@example.com', 'noregex'));
        self::assertFalse(PHPMailer::validateAddress('bad', 'noregex'));
    }
}
