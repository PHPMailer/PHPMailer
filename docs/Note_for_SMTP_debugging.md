# SMTP Debugging

If you are having problems connecting or sending emails through your SMTP server, the SMTP class can provide more information about the processing/errors taking place.
Use the debug functionality of the class to see what's going on in your connections. To do that, set the debug level in your script. For example:

```php
$mail->SMTPDebug = 2;
$mail->isSMTP();  // tell the class to use SMTP
$mail->SMTPAuth   = true;                // enable SMTP authentication
$mail->Port       = 25;                  // set the SMTP port
$mail->Host       = "mail.yourhost.com"; // SMTP server
$mail->Username   = "name@yourhost.com"; // SMTP account username
$mail->Password   = "your password";     // SMTP account password
```

##Debug levels

Setting the `SMTPDebug` property results in different amounts of output:

 * `0`: Disable debugging (you can also leave this out completely, 0 is the default).
 * `1`: Output messages sent by the client.
 * `2`: as 1, plus responses received from the server (this is probably the most useful setting for debugging).
 * `3`: as 2, plus more information about the initial connection.
 * `4`: as 3, plus even lower-level information, very verbose.

You don't need to use levels above 2 unless you're having trouble connecting at all - it will just make output more verbose and more difficult to read.

Note that you will get no output until you call `send()`, because no SMTP conversation takes place until you do that.

##Debug output format

The form that the debug output taks is determined by the `Debugoutput` property. This has several options:

 * `echo` Output plain-text as-is, appropriate for CLI
 * `html` Output escaped, line breaks converted to `<br>`, appropriate for browser output
 * `error_log` Output to error log as configured in php.ini

By default PHPMailer will use `echo` if run from a `cli` or `cli-server` SAPI, `html` otherwise. Alternatively, you can implement your own system by providing a callable expecting two parameters: a message string and the debug level:

    $mail->Debugoutput = function($str, $level) {echo "debug level $level; message: $str";};

You can of course make this more complex - for example your could capture all the output and store it in a database.

And finally, don't forget to disable debugging before going into production.
