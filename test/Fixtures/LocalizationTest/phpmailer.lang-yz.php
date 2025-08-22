<?php // lint < 8.5.

/**
 * Test fixture.
 *
 * Used in the `PHPMailer\LocalizationTest` to test that arbitrary code in translation files is disregarded.
 *
 * Note: this test fixture uses a syntax (backticks) which has been deprecated in PHP 8.5 and
 * is slated for removal in PHP 9.0.
 * For that reason, the file is excluded from the linting check on PHP 8.5 and above.
 */

$PHPMAILER_LANG['extension_missing'] = 'Confirming that test fixture was loaded correctly (yz).';
$PHPMAILER_LANG['encoding']          = `ls -l`;
