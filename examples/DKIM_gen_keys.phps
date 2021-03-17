<?php

/**
 * This shows how to make a new public/private key pair suitable for use with DKIM.
 * You should only need to do this once, and the public key (**not** the private key!)
 * you generate should be inserted in your DNS matching the selector you want.
 *
 * You can also use the DKIM wizard here: https://www.sparkpost.com/resources/tools/dkim-wizard/
 * but be aware that having your private key known anywhere outside your own server
 * is a security risk, and it's easy enough to create your own on your own server.
 *
 * For security, any keys you create should not be accessible via your web site.
 *
 * 2048 bits is the recommended minimum key length - gmail won't accept less than 1024 bits.
 * To test your DKIM config, use Sparkpost's DKIM tester:
 * https://tools.sparkpost.com/dkim
 *
 * Note that you only need a *private* key to *send* a DKIM-signed message,
 * but receivers need your *public* key in order to verify it.
 *
 * Your public key will need to be formatted appropriately for your DNS and
 * inserted there using the selector you want to use.
 */

//Set these to match your domain and chosen DKIM selector
$domain = 'example.com';
$selector = 'phpmailer';

//Private key filename for this selector
$privatekeyfile = $selector . '_dkim_private.pem';
//Public key filename for this selector
$publickeyfile = $selector . '_dkim_public.pem';

if (file_exists($privatekeyfile)) {
    echo "Using existing keys - if you want to generate new keys, delete old key files first.\n\n";
    $privatekey = file_get_contents($privatekeyfile);
    $publickey = file_get_contents($publickeyfile);
} else {
    //Create a 2048-bit RSA key with an SHA256 digest
    $pk = openssl_pkey_new(
        [
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]
    );
    //Save private key
    openssl_pkey_export_to_file($pk, $privatekeyfile);
    //Save public key
    $pubKey = openssl_pkey_get_details($pk);
    $publickey = $pubKey['key'];
    file_put_contents($publickeyfile, $publickey);
    $privatekey = file_get_contents($privatekeyfile);
}
echo "Private key (keep this private!):\n\n" . $privatekey;
echo "\n\nPublic key:\n\n" . $publickey;

//Prepare public key for DNS, e.g.
//phpmailer._domainkey.example.com IN TXT "v=DKIM1; h=sha256; t=s; p=" "MIIBIjANBg...oXlwIDAQAB"...
$dnskey = "$selector._domainkey.$domain IN TXT";
$dnsvalue = '"v=DKIM1; h=sha256; t=s; p=" ';
//Some DNS servers don't like ;(semi colon) chars unless backslash-escaped
$dnsvalue2 = '"v=DKIM1\; h=sha256\; t=s\; p=" ';

//Strip and split the key into smaller parts and format for DNS
//Many DNS systems don't like long TXT entries
//but are OK if it's split into 255-char chunks
//Remove PEM wrapper
$publickey = preg_replace('/^-+.*?-+$/m', '', $publickey);
//Strip line breaks
$publickey = str_replace(["\r", "\n"], '', $publickey);
//Split into chunks
$keyparts = str_split($publickey, 253); //Becomes 255 when quotes are included
//Quote each chunk
foreach ($keyparts as $keypart) {
    $dnsvalue .= '"' . trim($keypart) . '" ';
    $dnsvalue2 .= '"' . trim($keypart) . '" ';
}
echo "\n\nDNS key:\n\n" . trim($dnskey);
echo "\n\nDNS value:\n\n" . trim($dnsvalue);
echo "\n\nDNS value (with escaping):\n\n" . trim($dnsvalue2);
