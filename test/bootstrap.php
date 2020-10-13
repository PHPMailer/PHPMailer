<?php

/**
 * PHPUnit bootstrap file.
 */

ini_set('sendmail_path', '/usr/sbin/sendmail -t -i ');
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
} else {
    require_once '../vendor/autoload.php';
}
