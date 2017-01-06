<?php
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
} else {
    require_once '../vendor/autoload.php';
}
spl_autoload_register(function ($class) {
    require_once strtr($class, '\\_', '//').'.php';
});
