<?php

declare(strict_types=1);

/*
 *
 * The syntax for "esmtp-value" in [4] does not allow SP, "=", control
 * characters, or characters outside the traditional ASCII range of 1- 127
 * decimal to be transmitted in an esmtp-value. Because the ENVID and ORCPT
 * parameters may need to convey values outside this range, the esmtp-values for
 * these parameters are encoded as "xtext". "xtext" is formally defined as
 * follows:
 *
 * xtext = *( xchar / hexchar )
 *
 * xchar = any ASCII CHAR between "!" (33) and "~" (126) inclusive, except for
 *         "+" and "=".
 *
 * ; "hexchar"s are intended to encode octets that cannot appear
 * ; as ASCII characters within an esmtp-value.
 *
 * hexchar = ASCII "+" immediately followed by two upper case hexadecimal digits
 *
 * When encoding an octet sequence as xtext:
 *
 * + Any ASCII CHAR between "!" and "~" inclusive, except for "+" and "=",
 *   MAY be encoded as itself. (A CHAR in this range MAY instead be encoded
 *   as a "hexchar", at the implementor's discretion.)
 *
 * + ASCII CHARs that fall outside the range above must be encoded as "hexchar".
 */

include '../src/SMTP.php';

class SMTPTestDsn extends PHPMailer\PHPMailer\SMTP
{
    public function TestDsn()
    {
        $strs = [];
        $strs[] = 'RET=FULL ENVID=xyz';
        $strs[] = 'RET=FUll ENVID=xyz';
        $strs[] = 'ENVID=xyz';
        $strs[] = 'RET=ful ENVID=xyz';
        $strs[] = 'RET=Hdrs';
        $strs[] = 'RET=Hdrs ENVID=abcde+ée';
        $strs[] = 'RET=Hdrs ENVID=abc=de';
        $strs[] = 'RET=FULL ENVID=+Vincent+=+Jardin+=';

        foreach ($strs as $str) {
            printf("%s -> %s\n", $str, \PHPMailer\PHPMailer\SMTP::dsnize($str));
        }
    }
}

SMTPTestDsn::TestDsn();
