<?php
require_once dirname(dirname(__FILE__)).'/PHPMailerAutoload.php';
spl_autoload_register(function ($class) {
    require_once strtr($class, '\\_', '//').'.php';
});
