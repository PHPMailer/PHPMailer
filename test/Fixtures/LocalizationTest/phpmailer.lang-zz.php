<?php

/**
 * Test fixture.
 *
 * Used in the `PHPMailer\LocalizationTest` to test that language strings not
 * set via a fixed, known group of array index keys are disregarded.
 */

$PHPMAILER_LANG['extension_missing'] = 'Confirming that test fixture was loaded correctly (zz).';

// Keys not in the original array.
$PHPMAILER_LANG['unknown']           = 'Unknown text.';
$PHPMAILER_LANG['invalid']           = 'Invalid text.';

// Keys which exist in the original array, but use the wrong letter case or space instead of underscore.
$PHPMAILER_LANG['Authenticate']      = 'Overruled text, index not same case';
$PHPMAILER_LANG['CONNECT_HOST']      = 'Overruled text, index not same case';
$PHPMAILER_LANG['Data_Not_Accepted'] = 'Overruled text, index not same case';
$PHPMAILER_LANG['empty message']     = 'Overruled text, index not same case';
