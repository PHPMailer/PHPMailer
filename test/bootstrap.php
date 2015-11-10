<?php
require_once dirname(__DIR__).'/PHPMailerAutoload.php';
spl_autoload_register(function ($class) {
    require_once strtr($class, '\\_', '//').'.php';
});
