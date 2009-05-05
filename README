/*******************************************************************
* The http://phpmailer.codeworxtech.com/ website now carries a few *
* advertisements through the Google Adsense network. Please visit  *
* the advertiser sites and help us offset some of our costs.       *
* Thanks ....                                                      *
********************************************************************/

PHPMailer
Full Featured Email Transfer Class for PHP
==========================================

Version 5.0.0 (April 02, 2009)

With the release of this version, we are initiating a new version numbering
system to differentiate from the PHP4 version of PHPMailer.

Most notable in this release is fully object oriented code.

We now have available the PHPDocumentor (phpdocs) documentation. This is
separate from the regular download to keep file sizes down. Please see the
download area of http://phpmailer.codeworxtech.com.

We also have created a new test script (see /test_script) that you can use
right out of the box. Copy the /test_script folder directly to your server (in
the same structure ... with class.phpmailer.php and class.smtp.php in the
folder above it. Then launch the test script with:
http://www.yourdomain.com/phpmailer/test_script/index.php
from this one script, you can test your server settings for mail(), sendmail (or
qmail), and SMTP. This will email you a sample email (using contents.html for
the email body) and two attachments. One of the attachments is used as an inline
image to demonstrate how PHPMailer will automatically detect if attachments are
the same source as inline graphics and only include one version. Once you click
the Submit button, the results will be displayed including any SMTP debug
information and send status. We will also display a version of the script that
you can cut and paste to include in your projects. Enjoy!

Version 2.3 (November 08, 2008)

We have removed the /phpdoc from the downloads. All documentation is now on
the http://phpmailer.codeworxtech.com website.

The phpunit.php has been updated to support PHP5.

For all other changes and notes, please see the changelog.

Donations are accepted at PayPal with our id "paypal@worxteam.com".

Version 2.2 (July 15 2008)

- see the changelog.

Version 2.1 (June 04 2008)

With this release, we are announcing that the development of PHPMailer for PHP5
will be our focus from this date on. We have implemented all the enhancements
and fixes from the latest release of PHPMailer for PHP4.

Far more important, though, is that this release of PHPMailer (v2.1) is
fully tested with E_STRICT error checking enabled.

** NOTE: WE HAVE A NEW LANGUAGE VARIABLE FOR DIGITALLY SIGNED S/MIME EMAILS.
   IF YOU CAN HELP WITH LANGUAGES OTHER THAN ENGLISH AND SPANISH, IT WOULD BE
   APPRECIATED.

We have now added S/MIME functionality (ability to digitally sign emails).
BIG THANKS TO "sergiocambra" for posting this patch back in November 2007.
The "Signed Emails" functionality adds the Sign method to pass the private key
filename and the password to read it, and then email will be sent with
content-type multipart/signed and with the digital signature attached.

A quick note on E_STRICT:

- In about half the test environments the development version was subjected
  to, an error was thrown for the date() functions (used at line 1565 and 1569).
  This is NOT a PHPMailer error, it is the result of an incorrectly configured
  PHP5 installation. The fix is to modify your 'php.ini' file and include the
  date.timezone = America/New York
  directive, (for your own server timezone)
- If you do get this error, and are unable to access your php.ini file, there is
  a workaround. In your PHP script, add
  date_default_timezone_set('America/Toronto');

  * do NOT try to use
  $myVar = date_default_timezone_get();
  as a test, it will throw an error.

We have also included more example files to show the use of "sendmail", "mail()",
"smtp", and "gmail".

We are also looking for more programmers to join the volunteer development team.
If you have an interest in this, please let us know.

Enjoy!


Version 2.1.0beta1 & beta2

please note, this is BETA software
** DO NOT USE THIS IN PRODUCTION OR LIVE PROJECTS
INTENDED STRICTLY FOR TESTING

** NOTE:

As of November 2007, PHPMailer has a new project team headed by industry
veteran Andy Prevost (codeworxtech). The first release in more than two
years will focus on fixes, adding ease-of-use enhancements, provide
basic compatibility with PHP4 and PHP5 using PHP5 backwards compatibility
features. A new release is planned before year-end 2007 that will provide
full compatiblity with PHP4 and PHP5, as well as more bug fixes.

We are looking for project developers to assist in restoring PHPMailer to
its leadership position. Our goals are to simplify use of PHPMailer, provide
good documentation and examples, and retain backward compatibility to level
1.7.3 standards.

If you are interested in helping out, visit http://sourceforge.net/projects/phpmailer
and indicate your interest.

**

http://phpmailer.sourceforge.net/

This software is licenced under the LGPL.  Please read LICENSE for information on the
software availability and distribution.

Class Features:
- Send emails with multiple TOs, CCs, BCCs and REPLY-TOs
- Redundant SMTP servers
- Multipart/alternative emails for mail clients that do not read HTML email
- Support for 8bit, base64, binary, and quoted-printable encoding
- Uses the same methods as the very popular AspEmail active server (COM) component
- SMTP authentication
- Native language support
- Word wrap, and more!

Why you might need it:

Many PHP developers utilize email in their code.  The only PHP function
that supports this is the mail() function.  However, it does not expose
any of the popular features that many email clients use nowadays like
HTML-based emails and attachments. There are two proprietary
development tools out there that have all the functionality built into
easy to use classes: AspEmail(tm) and AspMail.  Both of these
programs are COM components only available on Windows.  They are also a
little pricey for smaller projects.

Since I do Linux development I�ve missed these tools for my PHP coding.
So I built a version myself that implements the same methods (object
calls) that the Windows-based components do. It is open source and the
LGPL license allows you to place the class in your proprietary PHP
projects.


Installation:

Copy class.phpmailer.php into your php.ini include_path. If you are
using the SMTP mailer then place class.smtp.php in your path as well.
In the language directory you will find several files like
phpmailer.lang-en.php.  If you look right before the .php extension
that there are two letters.  These represent the language type of the
translation file.  For instance "en" is the English file and "br" is
the Portuguese file.  Chose the file that best fits with your language
and place it in the PHP include path.  If your language is English
then you have nothing more to do.  If it is a different language then
you must point PHPMailer to the correct translation.  To do this, call
the PHPMailer SetLanguage method like so:

// To load the Portuguese version
$mail->SetLanguage("br", "/optional/path/to/language/directory/");

That's it.  You should now be ready to use PHPMailer!


A Simple Example:

<?php
require("class.phpmailer.php");

$mail = new PHPMailer();

$mail->IsSMTP();                                      // set mailer to use SMTP
$mail->Host = "smtp1.example.com;smtp2.example.com";  // specify main and backup server
$mail->SMTPAuth = true;     // turn on SMTP authentication
$mail->Username = "jswan";  // SMTP username
$mail->Password = "secret"; // SMTP password

$mail->From = "from@example.com";
$mail->FromName = "Mailer";
$mail->AddAddress("josh@example.net", "Josh Adams");
$mail->AddAddress("ellen@example.com");                  // name is optional
$mail->AddReplyTo("info@example.com", "Information");

$mail->WordWrap = 50;                                 // set word wrap to 50 characters
$mail->AddAttachment("/var/tmp/file.tar.gz");         // add attachments
$mail->AddAttachment("/tmp/image.jpg", "new.jpg");    // optional name
$mail->IsHTML(true);                                  // set email format to HTML

$mail->Subject = "Here is the subject";
$mail->Body    = "This is the HTML message body <b>in bold!</b>";
$mail->AltBody = "This is the body in plain text for non-HTML mail clients";

if(!$mail->Send())
{
   echo "Message could not be sent. <p>";
   echo "Mailer Error: " . $mail->ErrorInfo;
   exit;
}

echo "Message has been sent";
?>

CHANGELOG

See ChangeLog.txt

Download: http://sourceforge.net/project/showfiles.php?group_id=26031

Andy Prevost
