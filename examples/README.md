# PHPMailer code examples[![PHPMailer logo](images/phpmailer.png)](https://github.com/PHPMailer/PHPMailer)

This folder contains a collection of examples of using [PHPMailer](https://github.com/PHPMailer/PHPMailer).

## About testing email sending

When working on email sending code you'll find yourself worrying about what might happen if all these test emails got sent to your mailing list. The solution is to use a fake mail server, one that acts just like the real thing, but just doesn't actually send anything out. Some offer web interfaces, feedback, logging, the ability to return specific error codes, all things that are useful for testing error handling, authentication etc. Here's a selection of mail testing tools you might like to try:

*   [FakeSMTP](https://github.com/Nilhcem/FakeSMTP), a Java desktop app with the ability to show an SMTP log and save messages to a folder.
*   [FakeEmail](https://github.com/isotoma/FakeEmail), a Python-based fake mail server with a web interface.
*   [smtp-sink](http://www.postfix.org/smtp-sink.1.html), part of the Postfix mail server, so you may have this installed already. This is used in the Travis-CI configuration to run PHPMailer's unit tests.
*   [smtp4dev](http://smtp4dev.codeplex.com), a dummy SMTP server for Windows.
*   [fakesendmail.sh](https://github.com/PHPMailer/PHPMailer/blob/master/test/fakesendmail.sh), part of PHPMailer's test setup, this is a shell script that emulates sendmail for testing 'mail' or 'sendmail' methods in PHPMailer.
*   [msglint](http://tools.ietf.org/tools/msglint/), not a mail server, the IETF's MIME structure analyser checks the formatting of your messages.

Most of these examples use the `example.com` and `example.net` domains. These domains are reserved by IANA for illustrative purposes, as documented in [RFC 2606](http://tools.ietf.org/html/rfc2606). Don't use made-up domains like 'mydomain.com' or 'somedomain.com' in examples as someone, somewhere, probably owns them!

## Security note
Before running these examples in a web server, you'll need to rename them with '.php' extensions. They are supplied as '.phps' files which will usually be displayed with syntax highlighting by PHP instead of running them. This prevents potential security issues with running potential spam-gateway code if you happen to deploy these code examples on a live site - _please don't do that!_

Similarly, don't leave your passwords in these files as they will be visible to the world!

## [code_generator.phps](code_generator.phps)

This script is a simple code generator - fill in the form and hit submit, and it will use what you entered to email you a message, and will also generate PHP code using your settings that you can copy and paste to use in your own apps.

## [mail.phps](mail.phps)

This is a basic example which creates an email message from an external HTML file, creates a plain text body, sets various addresses, adds an attachment and sends the message. It uses PHP's built-in mail() function which is the simplest to use, but relies on the presence of a local mail server, something which is not usually available on Windows. If you find yourself in that situation, either install a local mail server, or use a remote one and send using SMTP instead.

## [simple_contact_form.phps](simple_contact_form.phps)

This is probably the most common reason for using PHPMailer - building a contact form. This example has a basic, unstyled form and also illustrates how to filter input data before using it, how to validate addresses, how to avoid being abused as a spam gateway, and how to address messages correctly so that you don't fail SPF checks.

## [exceptions.phps](exceptions.phps)

Like the mail example, but shows how to use PHPMailer's optional exceptions for error handling.

## [extending.phps](extending.phps)

This shows how to create a subclass of PHPMailer to customise its behaviour and simplify coding in your app.

## [smtp.phps](smtp.phps)

A simple example sending using SMTP with authentication.

## [smtp_no_auth.phps](smtp_no_auth.phps)

A simple example sending using SMTP without authentication.

## [send_file_upload.phps](send_file_upload.phps)

Lots of people want to do this... This is a simple form which accepts a file upload and emails it.

## [send_multiple_file_upload.phps](send_multiple_file_upload.phps)

A slightly more complex form that allows uploading multiple files at once and sends all of them as attachments to an email.

## [sendmail.phps](sendmail.phps)

A simple example using sendmail. Sendmail is a program (usually found on Linux/BSD, OS X and other UNIX-alikes) that can be used to submit messages to a local mail server without a lengthy SMTP conversation. It's probably the fastest sending mechanism, but lacks some error reporting features. There are sendmail emulators for most popular mail servers including postfix, qmail, exim etc.

## [gmail.phps](gmail.phps)

Submitting email via Google's Gmail service is a popular use of PHPMailer. It's much the same as normal SMTP sending, just with some specific settings, namely using TLS encryption, authentication is enabled, and it connects to the SMTP submission port 587 on the smtp.gmail.com host. This example does all that.

## [gmail_xoauth.phps](gmail_xoauth.phps)

Gmail now likes you to use XOAUTH2 for SMTP authentication. This is extremely laborious to set up, but once it's done you can use it repeatedly and will no longer need Gmail's ineptly-named "Allow less secure apps" setting enabled. [Read the guide in the wiki](https://github.com/PHPMailer/PHPMailer/wiki/Using-Gmail-with-XOAUTH2) for how to set it up.

## [pop_before_smtp.phps](pop_before_smtp.phps)

Back in the stone age, before effective SMTP authentication mechanisms were available, it was common for ISPs to use POP-before-SMTP authentication. As it implies, you authenticate using the POP3 protocol (an older protocol now mostly replaced by the far superior IMAP), and then the SMTP server will allow send access from your IP address for a short while, usually 5-15 minutes. PHPMailer includes a basic POP3 protocol client with just enough functionality to carry out this sequence - it's just like a normal SMTP conversation (without authentication), but connects via POP3 first.

## [mailing_list.phps](mailing_list.phps)

This is a somewhat naïve, but reasonably efficient example of sending similar emails to a list of different addresses. It sets up a PHPMailer instance using SMTP, then connects to a MySQL database to retrieve a list of recipients. The code loops over this list, sending email to each person using their info and marks them as sent in the database. It makes use of SMTP keepalive which saves reconnecting and re-authenticating between each message.

## [ssl_options.phps](ssl_options.phps)

PHP 5.6 introduced SSL certificate verification by default, and this applies to mail servers exactly as it does to web servers. Unfortunately, SSL misconfiguration in mail servers is quite common, so this caused a common problem: those that were previously using servers with bad configs suddenly found they stopped working when they upgraded PHP. PHPMailer provides a mechanism to disable SSL certificate verification as a workaround and this example shows how to do it. Bear in mind that this is **not** a good approach - the right way is to fix your mail server config!

## [smime_signed_mail.phps](smime_signed_mail.phps)

An example of how to sign messages using [S/MIME](https://en.wikipedia.org/wiki/S/MIME), ensuring that your data can't be tampered with in transit, and proves to recipients that it was you that sent it.

* * *

## [smtp_check.phps](smtp_check.phps)

This is an example showing how to use the SMTP class by itself (without PHPMailer) to check an SMTP connection.

## [smtp_low_memory.phps](smtp_low_memory.phps)

This demonstrates how to extend the SMTP class and make PHPMailer use it. In this case it's an effort to make the SMTP class use less memory when sending large attachments.

* * *
