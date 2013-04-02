# PHPMailer - A full-featured email creation and transfer class for PHP

Build status: [![Build Status](https://travis-ci.org/Synchro/PHPMailer.png)](https://travis-ci.org/Synchro/PHPMailer)

## Class Features

- Probably the world's most popular code for sending email from PHP!
- Used by many open-source projects: Drupal, SugarCRM, Yii, Joomla! and many more
- Integrated SMTP support - send without a local mail server
- Send emails with multiple TOs, CCs, BCCs and REPLY-TOs
- Multipart/alternative emails for mail clients that do not read HTML email
- Support for 8bit, base64, binary, and quoted-printable encoding
- SMTP authentication with LOGIN, PLAIN, NTLM and CRAM-MD5 mechanisms
- Native language support
- Compatible with PHP 5.0 and later
- Much more!

## Why you might need it

Many PHP developers utilize email in their code. The only PHP function that supports this is the mail() function. However, it does not provide any assistance for making use of popular features such as HTML-based emails and attachments.

Formatting email correctly is surprisingly difficult. There are myriad overlapping RFCs, requiring tight adherence to horribly complicated formatting and encoding rules - the vast majority of code that you'll find online that uses the mail() function directly is just plain wrong!
*Please* don't be tempted to do it yourself - if you don't use PHPMailer, there are many other excellent libraries that you should look at before rolling your own - try SwiftMailer, Zend_Mail, eZcomponents etc.

The PHP mail() function usually sends via a local mail server, typically fronted by a `sendmail` binary on Linux, BSD and OS X platforms, however, Windows usually doesn't include a local mail server; PHPMailer's integrated SMTP implementation allows email sending on Windows platforms without a local mail server.

## License

This software is licenced under the [LGPL 2.1](http://www.gnu.org/licenses/lgpl-2.1.html). Please read LICENSE for information on the
software availability and distribution.

## Installation

PHPMailer is available via [Composer/Packagist](https://packagist.org/packages/phpmailer/phpmailer). Alternatively, just copy the contents of the PHPMailer folder into somewhere that's in your PHP `include_path` setting. If you don't speak git or just want a tarball, click the 'zip' button at the top of the page in GitHub.


## A Simple Example

```php
<?php
require 'class.phpmailer.php';

$mail = new PHPMailer;

$mail->IsSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'smtp1.example.com;smtp2.example.com';  // Specify main and backup server
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'jswan';                            // SMTP username
$mail->Password = 'secret';                           // SMTP password
$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted

$mail->From = 'from@example.com';
$mail->FromName = 'Mailer';
$mail->AddAddress('josh@example.net', 'Josh Adams');  // Add a recipient
$mail->AddAddress('ellen@example.com');               // Name is optional
$mail->AddReplyTo('info@example.com', 'Information');
$mail->AddCC('cc@example.com');
$mail->AddBCC('bcc@example.com');

$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
$mail->AddAttachment('/var/tmp/file.tar.gz');         // Add attachments
$mail->AddAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
$mail->IsHTML(true);                                  // Set email format to HTML

$mail->Subject = 'Here is the subject';
$mail->Body    = 'This is the HTML message body <b>in bold!</b>';
$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

if(!$mail->Send()) {
   echo 'Message could not be sent.';
   echo 'Mailer Error: ' . $mail->ErrorInfo;
   exit;
}

echo 'Message has been sent';
```

You'll find plenty more to play with in the `examples` folder.

That's it. You should now be ready to use PHPMailer!

## Localization
PHPMailer defaults to English, but in the `languages` folder you'll find numerous translations for PHPMailer error messages that you may encounter. Their filenames contain [ISO 639-1](http://en.wikipedia.org/wiki/ISO_639-1) language code for the translations, for example `fr` for French. To specify a language, you need to tell PHPMailer which one to use, like this:

```php
// To load the French version
$mail->SetLanguage('fr', '/optional/path/to/language/directory/');
```

## Documentation

You'll find some basic user-level docs in the docs folder, and you can generate complete API-level documentation using the `generatedocs.sh` shell script in the docs folder, though you'll need to install [PHPDocumentor](http://www.phpdoc.org) first.

## Tests

You'll find a PHPUnit test script in the `test` folder.

Build status: [![Build Status](https://travis-ci.org/Synchro/PHPMailer.png)](https://travis-ci.org/Synchro/PHPMailer)

If this isn't passing, is there something you can do to help?

## Contributing

Please submit bug reports, suggestions and pull requests to the [GitHub issue tracker](https://github.com/Synchro/PHPMailer/issues).

We're particularly interested in fixing edge-cases, expanding test coverage and updating translations.

Please *don't* use the SourceForge project any more.

## Changelog

See changelog.md

## History
- PHPMailer was originally written in 2001 by Brent R. Matzelle as a [SourceForge project](http://sourceforge.net/projects/phpmailer/).
- Marcus Bointon (coolbru on SF) and Andy Prevost (codeworxtech) took over the project in 2004.
- Became an Apache incubator project on Google Code in 2010, managed by Jim Jagielski
- Marcus started this fork on [GitHub](https://github.com/Synchro/PHPMailer)
- Jim and Marcus decide to join forces and use GitHub as the canonical and official repo for PHPMailer.

### What's changed since moving from SourceForge?
- Official successor to the SourceForge and Google Code projects
- Test suite
- Continuous integration with Travis-CI
- Composer support
- Rolling releases
- Additional languages and language strings
- CRAM-MD5 authentication support
- Preserves full repo history of authors, commits and branches from the original SourceForge project
