![PHPMailer](https://raw.github.com/PHPMailer/PHPMailer/master/examples/images/phpmailer.png)

# PHPMailer - A full-featured email creation and transfer class for PHP

Build status: [![Build Status](https://travis-ci.org/PHPMailer/PHPMailer.svg)](https://travis-ci.org/PHPMailer/PHPMailer)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/PHPMailer/PHPMailer/badges/quality-score.png?s=3758e21d279becdf847a557a56a3ed16dfec9d5d)](https://scrutinizer-ci.com/g/PHPMailer/PHPMailer/)
[![Code Coverage](https://scrutinizer-ci.com/g/PHPMailer/PHPMailer/badges/coverage.png?s=3fe6ca5fe8cd2cdf96285756e42932f7ca256962)](https://scrutinizer-ci.com/g/PHPMailer/PHPMailer/)

[![Latest Stable Version](https://poser.pugx.org/phpmailer/phpmailer/v/stable.svg)](https://packagist.org/packages/phpmailer/phpmailer) [![Total Downloads](https://poser.pugx.org/phpmailer/phpmailer/downloads)](https://packagist.org/packages/phpmailer/phpmailer) [![Latest Unstable Version](https://poser.pugx.org/phpmailer/phpmailer/v/unstable.svg)](https://packagist.org/packages/phpmailer/phpmailer) [![License](https://poser.pugx.org/phpmailer/phpmailer/license.svg)](https://packagist.org/packages/phpmailer/phpmailer)

## Class Features

- Probably the world's most popular code for sending email from PHP!
- Used by many open-source projects: WordPress, Drupal, 1CRM, SugarCRM, Yii, Joomla! and many more
- Integrated SMTP support - send without a local mail server
- Send emails with multiple To, CC, BCC and Reply-to addresses
- Multipart/alternative emails for mail clients that do not read HTML email
- Support for UTF-8 content and 8bit, base64, binary, and quoted-printable encodings
- SMTP authentication with LOGIN, PLAIN, NTLM, CRAM-MD5 and Google's XOAUTH2 mechanisms over SSL and TLS transports
- Validates email addresses automatically
- Protect against header injection attacks
- Error messages in 47 languages!
- DKIM and S/MIME signing support
- Compatible with PHP 5.5 and later
- Much more!

## Why you might need it

Many PHP developers utilize email in their code. The only PHP function that supports this is the `mail()` function. However, it does not provide any assistance for making use of popular features such as HTML-based emails and attachments.

Formatting email correctly is surprisingly difficult. There are myriad overlapping RFCs, requiring tight adherence to horribly complicated formatting and encoding rules - the vast majority of code that you'll find online that uses the `mail()` function directly is just plain wrong!
*Please* don't be tempted to do it yourself - if you don't use PHPMailer, there are many other excellent libraries that you should look at before rolling your own - try SwiftMailer, Zend_Mail, eZcomponents etc.

The PHP `mail()` function usually sends via a local mail server, typically fronted by a `sendmail` binary on Linux, BSD and OS X platforms, however, Windows usually doesn't include a local mail server; PHPMailer's integrated SMTP implementation allows email sending on Windows platforms without a local mail server.

## License

This software is distributed under the [LGPL 2.1](http://www.gnu.org/licenses/lgpl-2.1.html) license. Please read LICENSE for information on the
software availability and distribution.

## Installation & loading

PHPMailer is available on [Packagist](https://packagist.org/packages/phpmailer/phpmailer) (using semantic versioning), and installation via composer is the recommended way to install PHPMailer. Just add this line to your `composer.json` file:

```json
"phpmailer/phpmailer": "~6.0"
```

or run

```sh
composer require phpmailer/phpmailer
```

PHPMailer declares the namespace `PHPMailer\PHPMailer`.

If you want to use the Gmail XOAUTH2 authentication class, you will also need to add a dependency on the `league/oauth2-client` package in your `composer.json`.

Alternatively, if you're not using composer, copy the contents of the PHPMailer folder into one of the `include_path` directories specified in your PHP configuration and load each one manually.

If you don't speak git or just want a tarball, click the 'zip' button on the right of the project page in GitHub.

## Legacy versions
PHPMailer 5.2 (which is compatible with PHP 5.0 - 7.0) is no longer being supported for feature updates, and will only be receiving security updates from now on. You will find the latest version of 5.2 in the [5.2-stable branch](https://github.com/PHPMailer/PHPMailer/tree/5.2-stable), and future versions of 5.2 will be tagged with 5.2.x version numbers, so existing composer configs should remain working. If you're using PHP 5.5 or later, we recommend you make the necessary changes to switch to the 6.0 release.

### Minimal installation

While installing the entire package manually or with composer is simple, convenient and reliable, you may want to include only vital files in your project. At the very least you will need [src/PHPMailer.php](https://github.com/PHPMailer/PHPMailer/tree/master/src/PHPMailer.php). If you're using SMTP, you'll need [src/SMTP.php](https://github.com/PHPMailer/PHPMailer/tree/master/src/SMTP.php), and if you're using POP-before SMTP, you'll need [src/POP3.php](https://github.com/PHPMailer/PHPMailer/tree/master/src/POP3.php). You can skip the [language](https://github.com/PHPMailer/PHPMailer/tree/master/language/) folder if you're not showing errors to users and can make do with English-only errors. If you're using XOAUTH2 you will need [src/OAuth.php](https://github.com/PHPMailer/PHPMailer/tree/master/src/OAuth.php) as well as the composer dependencies for the services you wish to authenticate with. Really, it's much easier to use composer!

## A Simple Example

```php
<?php
//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

$mail = new PHPMailer(true);
try {
    //Server settings
    $mail->SMTPDebug = 2;                                 // Enable verbose debug output
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = 'smtp1.example.com;smtp2.example.com';  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = 'user@example.com';                 // SMTP username
    $mail->Password = 'secret';                           // SMTP password
    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 587;                                    // TCP port to connect to

    //Recipients
    $mail->setFrom('from@example.com', 'Mailer');
    $mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient
    $mail->addAddress('ellen@example.com');               // Name is optional
    $mail->addReplyTo('info@example.com', 'Information');
    $mail->addCC('cc@example.com');
    $mail->addBCC('bcc@example.com');

    //Attachments
    $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
    $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

    //Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = 'Here is the subject';
    $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
}
```

You'll find plenty more to play with in the [examples](https://github.com/PHPMailer/PHPMailer/tree/master/examples) folder.

That's it. You should now be ready to use PHPMailer!

## Localization
PHPMailer defaults to English, but in the [language](https://github.com/PHPMailer/PHPMailer/tree/master/language/) folder you'll find numerous (46 at the time of writing!) translations for PHPMailer error messages that you may encounter. Their filenames contain [ISO 639-1](http://en.wikipedia.org/wiki/ISO_639-1) language code for the translations, for example `fr` for French. To specify a language, you need to tell PHPMailer which one to use, like this:

```php
// To load the French version
$mail->setLanguage('fr', '/optional/path/to/language/directory/');
```

We welcome corrections and new languages - if you're looking for corrections to do, run the [phpmailerLangTest.php](https://github.com/PHPMailer/PHPMailer/tree/master/test/phpmailerLangTest.php) script in the tests folder and it will show any missing translations.

## Documentation

Start reading at the [GitHub wiki](https://github.com/PHPMailer/PHPMailer/wiki). If you're having trouble, this should be the first place you look as it's the most frequently updated.

Examples of how to use PHPMailer for common scenarios can be found in the [examples](https://github.com/PHPMailer/PHPMailer/tree/master/examples) folder. If you're looking for a good starting point, we recommend you start with [the Gmail example](https://github.com/PHPMailer/PHPMailer/tree/master/examples/gmail.phps).

Complete generated API documentation is [available online](http://phpmailer.github.io/PHPMailer/).

You can generate complete API-level documentation by running `phpdoc` in the top-level folder, and documentation will appear in teh `docs` folder, though you'll need to have [PHPDocumentor](http://www.phpdoc.org) installed. You may find [the unit tests](https://github.com/PHPMailer/PHPMailer/tree/master/test/phpmailerTest.php) a good source of how to do various operations such as encryption.

If the documentation doesn't cover what you need, search the [many questions on Stack Overflow](http://stackoverflow.com/questions/tagged/phpmailer), and before you ask a question about "SMTP Error: Could not connect to SMTP host.", [read the troubleshooting guide](https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting).

## Tests

There is a PHPUnit test script in the [test](https://github.com/PHPMailer/PHPMailer/tree/master/test/) folder. PHPMailer uses PHPUnit 4.8 - we would use 5.x but we need to run on PHP 5.5.

Build status: [![Build Status](https://travis-ci.org/PHPMailer/PHPMailer.svg)](https://travis-ci.org/PHPMailer/PHPMailer)

If this isn't passing, is there something you can do to help?

## Contributing

Please submit bug reports, suggestions and pull requests to the [GitHub issue tracker](https://github.com/PHPMailer/PHPMailer/issues).

We're particularly interested in fixing edge-cases, expanding test coverage and updating translations.

If you have git clones from prior to the move to the PHPMailer GitHub organisation, you'll need to update any remote URLs referencing the old GitHub location with a command like this from within your clone:

```sh
git remote set-url upstream https://github.com/PHPMailer/PHPMailer.git
```

Please *don't* use the SourceForge or Google Code projects any more; they are obsolete and no longer maintained.

## Sponsorship

Development time and resources for PHPMailer are provided by [Smartmessages.net](https://info.smartmessages.net/), a powerful email marketing system.

<a href="https://info.smartmessages.net/"><img src="https://www.smartmessages.net/img/smartmessages-logo.svg" width="250" height="28" alt="Smartmessages email marketing"></a>

Other contributions are gladly received, whether in beer 🍺, T-shirts 👕, Amazon wishlist raids, or cold, hard cash 💰.

## Changelog

See [changelog](changelog.md).

## History
- PHPMailer was originally written in 2001 by Brent R. Matzelle as a [SourceForge project](http://sourceforge.net/projects/phpmailer/).
- Marcus Bointon (coolbru on SF) and Andy Prevost (codeworxtech) took over the project in 2004.
- Became an Apache incubator project on Google Code in 2010, managed by Jim Jagielski.
- Marcus created his fork on [GitHub](https://github.com/Synchro/PHPMailer).
- Jim and Marcus decide to join forces and use GitHub as the canonical and official repo for PHPMailer.
- PHPMailer moves to the [PHPMailer organisation](https://github.com/PHPMailer) on GitHub.

### What's changed since moving from SourceForge?
- Official successor to the SourceForge and Google Code projects.
- Test suite.
- Continuous integration with Travis-CI.
- Composer support.
- Public development.
- Additional languages and language strings.
- CRAM-MD5 authentication support.
- Preserves full repo history of authors, commits and branches from the original SourceForge project.
