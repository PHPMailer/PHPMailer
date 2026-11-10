<?php

/**
 * Replacement for PHPMailer::parseAddresses().
 *
 * PHPMailer::parseAddresses() is deprecated and will be removed in PHPMailer 8.0
 * because its full parser depended on the IMAP extension (removed in PHP 8.4),
 * while the fallback parser is just explode(',', ...) and breaks on real-world
 * headers, e.g. quoted names containing commas: '"Doe, John" <john@example.com>'.
 *
 * Copy the parseAddressesReplacement() function below into your own project.
 * It returns the same shape as PHPMailer::parseAddresses():
 *   [['name' => 'Joe User', 'address' => 'joe@example.com'], ...]
 * Invalid addresses are skipped, exactly like the original.
 *
 * What it handles over the old fallback parser:
 * - quoted display names containing commas ('"Doe, John" <john@example.com>')
 * - address groups ('Friends: a@example.com, b@example.com;')
 * - (comments) and folded (multi-line) headers
 * - RFC2047 encoded display names, via PHPMailer::decodeHeader()
 *
 * Requires: PHPMailer (for validateAddress() and decodeHeader()).
 */

//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Split an address list on commas/semicolons that are NOT inside
 * double quotes, angle brackets or (comments).
 *
 * @param string $addrstr
 *
 * @return string[]
 */
function splitAddressList($addrstr)
{
    $parts = [];
    $current = '';
    $inQuotes = false;
    $angleDepth = 0;
    $parenDepth = 0;
    $len = strlen($addrstr);
    for ($i = 0; $i < $len; ++$i) {
        $c = $addrstr[$i];
        if ($c === '\\' && $inQuotes && $i + 1 < $len) {
            $current .= $c . $addrstr[++$i];
            continue;
        }
        if ($c === '"') {
            $inQuotes = !$inQuotes;
        } elseif (!$inQuotes) {
            if ($c === '<') {
                ++$angleDepth;
            } elseif ($c === '>') {
                $angleDepth = max(0, $angleDepth - 1);
            } elseif ($c === '(') {
                ++$parenDepth;
            } elseif ($c === ')') {
                $parenDepth = max(0, $parenDepth - 1);
            } elseif (($c === ',' || $c === ';') && $angleDepth === 0 && $parenDepth === 0) {
                $parts[] = $current;
                $current = '';
                continue;
            }
        }
        $current .= $c;
    }
    $parts[] = $current;

    return $parts;
}

/**
 * Drop-in replacement for PHPMailer::parseAddresses().
 * Copy this function (with splitAddressList() above) into your project.
 *
 * @param string $addrstr The address list string
 * @param string $charset Charset used when decoding display names
 *
 * @return array{name: string, address: string}[]
 */
function parseAddressesReplacement($addrstr, $charset = PHPMailer::CHARSET_ISO88591)
{
    //Unfold folded headers (CRLF followed by whitespace)
    $addrstr = preg_replace('/\r\n[ \t]+/', ' ', (string) $addrstr);
    $addresses = [];
    foreach (splitAddressList($addrstr) as $part) {
        $part = trim($part);
        if ($part === '') {
            continue;
        }
        //Strip a group name, e.g. 'Friends:' in 'Friends: a@x, b@y;'
        if (preg_match('/^[^<>:;,"]+:(.*)$/s', $part, $m)) {
            $part = trim($m[1]);
            if ($part === '') {
                continue; //Empty group, e.g. 'undisclosed-recipients:;'
            }
        }
        //Strip (comments), innermost first so nested ones resolve
        $previous = null;
        while ($previous !== $part) {
            $previous = $part;
            $part = trim(preg_replace('/\([^()]*\)/', ' ', $part));
        }
        if ($part === '') {
            continue;
        }
        //Separate 'Display Name <address>' from a bare address
        if (preg_match('/^(.*)<\s*([^<>]+)\s*>$/s', $part, $m)) {
            $name = trim($m[1]);
            $email = trim($m[2]);
        } else {
            $name = '';
            $email = $part;
        }
        if (!PHPMailer::validateAddress($email)) {
            continue;
        }
        $addresses[] = [
            'name' => trim(PHPMailer::decodeHeader($name, $charset), '\'" '),
            'address' => $email,
        ];
    }

    return $addresses;
}

//Demo - delete this block when copying the functions into your project
if (PHP_SAPI === 'cli' && realpath($_SERVER['argv'][0] ?? '') === __FILE__) {
    require '../vendor/autoload.php';

    $samples = [
        'joe@example.com',
        'Joe User <joe@example.com>, Jill User <jill@example.net>',
        '"Doe, John" <john@example.com>, jane@example.com',
        'Friends: a@example.com, "Bee, Bea" <b@example.com>;',
        '=?UTF-8?Q?J=C3=B6rg_M=C3=BCller?= <xyz@example.com>',
        'Tim "The Book" O\'Reilly <foo@example.com> (my comment)',
        'Jill User <doug@>, valid@example.com',
        'undisclosed-recipients:;',
    ];
    foreach ($samples as $s) {
        echo $s, "\n";
        print_r(parseAddressesReplacement($s, PHPMailer::CHARSET_UTF8));
    }
}
