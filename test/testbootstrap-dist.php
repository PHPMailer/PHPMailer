<?php

$_REQUEST['submitted'] = 1;
$_REQUEST['mail_to'] = 'somebody@example.com';
$_REQUEST['mail_from'] = 'phpunit@example.com';
$_REQUEST['mail_cc'] = 'cc@example.com';
$_REQUEST['mail_host'] = '127.0.0.1';
$_REQUEST['mail_port'] = 2525;
//Trust the test server's self-signed certificate so SMTPAutoTLS can negotiate STARTTLS.
//This is safe because the test server is a local, deliberately broken SMTP implementation.
$_REQUEST['mail_options'] = [
    'ssl' => [
        'verify_peer'       => false,
        'verify_peer_name'  => false,
        'allow_self_signed' => true,
    ],
];
