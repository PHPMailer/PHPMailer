<?php

/**
 * Test fixture.
 *
 * Used in the `PHPMailer\LocalizationTest` to test that arbitrary code in translation files is disregarded.
 */

$composer = file_get_contents(__DIR__ . '/../../../composer.json');

echo $composer;

$PHPMAILER_LANG['extension_missing'] = 'Confirming that test fixture was loaded correctly (yy).';
$PHPMAILER_LANG['empty_message']     = $composer;
$PHPMAILER_LANG['encoding']          = `ls -l`;
$PHPMAILER_LANG['execute']           = exec('some harmful command');
$PHPMAILER_LANG['signing']           = "Double quoted but not interpolated $composer";
