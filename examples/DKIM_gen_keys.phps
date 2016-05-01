<?php
/**
 * This shows how to make a new public/private key pair suitable for use with DKIM.
 * You should only need to do this once, and the public key (**not** the private key!)
 * you generate should be inserted in your DNS matching the selector you want.
 *
 * You can also use the DKIM wizard here: https://www.port25.com/support/domainkeysdkim-wizard/
 * but be aware that having your private key known anywhere outside your own server
 * is a security risk, and it's easy enough to create your own on your own server.
 *
 * For security, any keys you create should not be accessible via your web site.
 *
 * 2048 bits is the recommended minimum key length - gmail won't accept less than 1024 bits.
 * To test your DKIM config, use Port25's DKIM tester:
 * https://www.port25.com/support/authentication-center/email-verification/
 *
 * Note that you only need a *private* key to *send* a DKIM-signed message,
 * but receivers need your *public* key in order to verify it.
 *
 * Your public key will need to be formatted appropriately for your DNS and
 * inserted there using the selector you want to use.
 */

//Path to your private key:
$privatekeyfile = 'dkim_private.pem';
//Path to your public key:
$publickeyfile = 'dkim_public.pem';

//Create a 2048-bit RSA key with an SHA256 digest
$pk = openssl_pkey_new(
    [
        'digest_alg'       => 'sha256',
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA
    ]
);
//Save private key
openssl_pkey_export_to_file($pk, $privatekeyfile);
//Save public key
$pubKey = openssl_pkey_get_details($pk);
file_put_contents($publickeyfile, $pubKey['key']);

echo file_get_contents($privatekeyfile);
echo $pubKey['key'];
