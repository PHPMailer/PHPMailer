<?php
/**
 * PHPUnit bootstrap file
 */

ini_set('sendmail_path', '/usr/sbin/sendmail -t -i ');
require_once '../vendor/autoload.php';
spl_autoload_register(
    function ($class) {
        require_once strtr($class, '\\_', '//') . '.php';
    }
);
