# PHPMailer Change Log

## Version 6.4.1 (April 29th, 2021)
* **SECURITY** Fixes CVE-2020-36326, a regression of CVE-2018-19296 object injection introduced in 6.1.8, see SECURITY.md for details
* Reject more file paths that look like URLs, matching RFC3986 spec, blocking URLS using schemes such as `ssh2`
* Ensure method signature consistency in `doCallback` calls
* Ukrainian language update
* Add composer scripts for checking coding standards and running tests

## Version 6.4.0 (March 31st, 2021)
* Revert change that made the `mail()` and sendmail transports set the envelope sender if one isn't explicitly provided, as it causes problems described in <https://github.com/PHPMailer/PHPMailer/issues/2298>
* Check for mbstring extension before decoding addresss in `parseAddress`
* Add Serbian Latin translation (`sr_latn`)
* Enrol PHPMailer in Tidelift

## Version 6.3.0 (February 19th, 2021)
* Handle early connection errors such as 421 during connection and EHLO states
* Switch to Github Actions for CI
* Generate debug output for `mail()`, sendmail, and qmail transports. Enable using the same mechanism as for SMTP: set `SMTPDebug` > 0
* Make the `mail()` and sendmail transports set the envelope sender the same way as SMTP does, i.e. use whatever `From` is set to, only falling back to the `sendmail_from` php.ini setting if `From` is unset. This avoids errors from the `mail()` function if `Sender` is not set explicitly and php.ini is not configured. This is a minor functionality change, so bumps the minor version number.
* Extend `parseAddresses` to decode encoded names, improve tests

## Version 6.2.0
* PHP 8.0 compatibility, many thanks to @jrf_nl!
* Switch from PHP CS Fixer to PHP CodeSniffer for coding standards
* Create class constants for the debug levels in the POP3 class
* Improve French, Slovenian, and Ukrainian translations
* Improve file upload examples so file extensions are retained
* Resolve PHP 8 line break issues due to a very old PHP bug being fixed
* Avoid warnings when using old openssl functions
* Improve Travis-CI build configuration

## Version 6.1.8 (October 9th, 2020)
* Mark `ext-hash` as required in composer.json. This has long been required, but now it will cause an error at install time rather than runtime, making it easier to diagnose
* Make file upload examples safer
* Update links to SMTP testing servers
* Avoid errors when set_time_limit is disabled (you need better hosting!)
* Allow overriding auth settings for local tests; makes it easy to run tests using HELO
* Recover gracefully from errors during keepalive sessions
* Add AVIF MIME type mapping
* Prevent duplicate `To` headers in BCC-only messages when using `mail()`
* Avoid file function problems when attaching files from Windows UNC paths
* Improve German, Bahasa Indonesian, Filipino translations
* Add Javascript-based example
* Increased test coverage

## Version 6.1.7 (July 14th, 2020)
* Split SMTP connection into two separate methods
* Undo BC break in PHP versions 5.2.3 - 7.0.0 introduced in 6.1.2 when injecting callables for address validation and HTML to text conversion
* Save response to SMTP welcome banner as other responses are saved
* Retry stream_select if interrupted by a signal

## Version 6.1.6 (May 27th, 2020)
* **SECURITY** Fix insufficient output escaping bug in file attachment names. CVE-2020-13625. Reported by Elar Lang of Clarified Security.
* Correct Armenian ISO language code from `am` to `hy`, add mapping for fallback
* Use correct timeout property in debug output

## Version 6.1.5 (March 14th, 2020)
* Reject invalid custom headers that are empty or contain breaks
* Various fixes for DKIM issues, especially when using `mail()` transport
* Drop the `l=` length tag from DKIM signatures; it's a mild security risk
* Ensure CRLF is used explicitly when needed, rather than `static::$LE`
* Add a method for trimming header content consistently
* Some minor tweaks to resolve static analyser complaints
* Check that attachment files are readable both when adding *and* when sending
* Work around Outlook bug in mishandling MIME preamble
* Danish translation improvements

## Version 6.1.4 (December 10th, 2019)
* Clean up hostname handling
* Avoid IDN error on older PHP versions, prep for PHP 8.0
* Don't force RFC2047 folding unnecessarily
* Enable tests on full release of PHP 7.4

## Version 6.1.3 (November 21st, 2019) 
* Fix an issue preventing injected debug handlers from working
* Fix an issue relating to connection timeout
* Add `SMTP::MAX_REPLY_LENGTH` constant
* Remove some dev dependencies; phpdoc no longer included
* Fix an issue where non-compliant servers returning bare codes caused an SMTP hang

## Version 6.1.2 (November 13th, 2019) 
* Substantial revision of DKIM header generation
* Use shorter hashes for auto-generated CID values
* Fix format of content-id headers, and only use them for inline attachments
* Remove all use of XHTML
* Lots of coding standards cleanup
* API docs are now auto-updated via GitHub actions
* Fix header separation bug created in 6.1.1
* Fix misidentification of background attributes in SVG images in msgHTML

## Version 6.1.1 (September 27th 2019)
* Fix misordered version tag

## Version 6.1.0 (September 27th 2019)
* Multiple bug fixes for folding of long header lines, thanks to @caugner
* Add support for [RFC2387 child element content-type hint](https://tools.ietf.org/html/rfc2387#section-3.1) in `multipart/related` structures.
* Support for Ical event methods other than `REQUEST`, thanks to @puhr-mde
* Change header folding and param separation to use spaces instead of tabs
* Use ; to separate multiple MIME header params
* Add support for RFC3461 DSN messages
* IMAP example code fixed
* Use PHP temp streams instead of temp files
* Allow for longer SMTP error codes
* Updated Brazilian Portuguese translation
* Throw exceptions on invalid encoding values
* Add Afrikaans translation, thanks to @Donno191
* Updated Farsi/Persian translation
* Add PHP 7.4 to test config
* Remove some ambiguity about setting XMailer property
* Improve error checking in mailing list example
* Drop PHP 5.5 from CI config as it's no longer supported by Travis-CI
* Fix S/MIME signing
* Add constants for encryption type
* More consistent use of constants for encryption, charset, encoding
* Add PHPMailer logo images

## Version 6.0.7 (February 1st 2019)
* Include RedHat GPL Cooperation Commitment - see the `COMMITMENT` file for details.
* Don't exclude composer.json from git exports as it breaks composer updates in projects that use PHPMailer
* Updated Malay translation
* Fix language tests

## Version 6.0.6 (November 14th 2018)
* **SECURITY** Fix potential object injection vulnerability. Reported by Sehun Oh of cyberone.kr.
* Added Tagalog translation, thanks to @StoneArtz
* Added Malagache translation, thanks to @Hackinet
* Updated Serbian translation, fixed incorrect language code, thanks to @mmilanovic4
* Updated Arabic translations (@MicroDroid)
* Updated Hungarian translations
* Updated Dutch translations
* Updated Slovenian translation (@filips123)
* Updated Slovak translation (@pcmanik)
* Updated Italian translation (@sabas)
* Updated Norwegian translation (@aleskr)
* Updated Indonesian translation (@mylastof)
* Add constants for common values, such as `text/html` and `quoted-printable`, and use them
* Added support for copied headers in DKIM, helping with debugging, and an option to add extra headers to the DKIM signature. See DKIM_sign example for how to use them. Thanks to @gwi-mmuths.
* Add Campaign Monitor transaction ID pattern matcher
* Remove deprecated constant and ini values causing warnings in PHP 7.3, added PHP 7.3 build to Travis config.
* Expanded test coverage

## Version 5.2.27 (November 14th 2018)
* **SECURITY** Fix potential object injection vulnerability. Reported by Sehun Oh of cyberone.kr.
* Note that the 5.2 branch is now deprecated and will not receive security updates after 31st December 2018.

## Version 6.0.5 (March 27th 2018)
* Re-roll of 6.0.4 to fix missed version file entry. No code changes.

## Version 6.0.4 (March 27th 2018)
* Add some modern MIME types
* Add Hindi translation (thanks to @dextel2)
* Improve composer docs
* Fix generation of path to language files

## Version 6.0.3 (January 5th 2018)
* Correct DKIM canonicalization of line breaks for header & body - thanks to @themichaelhall
* Make dependence on ext-filter explicit in composer.json

## Version 6.0.2 (November 29th 2017)
* Don't make max line length depend on line break format
* Improve Travis-CI config - thanks to Filippo Tessarotto
* Match SendGrid transaction IDs
* `idnSupported()` now static, as previously documented
* Improve error messages for invalid addresses
* Improve Indonesian translation (thanks to @januridp)
* Improve Esperanto translation (thanks to @dknacht)
* Clean up git export ignore settings for production and zip bundles
* Update license doc
* Updated upgrading docs
* Clarify `addStringEmbeddedImage` docs
* Hide auth credentials in all but lowest level debug output, prevents leakage in bug reports
* Code style cleanup

## Version 6.0.1 (September 14th 2017)
* Use shorter Message-ID headers (with more entropy) to avoid iCloud blackhole bug
* Switch to Symfony code style (though it's not well defined)
* CI builds now apply syntax & code style checks, so make your PRs tidy!
* CI code coverage only applied on latest version of PHP to speed up builds (thanks to @Slamdunk for these CI changes)
* Remove `composer.lock` - it's important that libraries break early; keeping it is for apps
* Rename test scripts to PSR-4 spec
* Make content-id values settable on attachments, not just embedded items
* Add SMTP transaction IDs to callbacks & allow for future expansion
* Expand test coverage

## Version 6.0 (August 28th 2017)
This is a major update that breaks backwards compatibility.

* **Requires PHP 5.5 or later**
* **Uses the `PHPMailer\PHPMailer` namespace**
* File structure simplified and PSR-4 compatible, classes live in the `src/` folder
* The custom autoloader has been removed: [**use composer**](https://getcomposer.org)!
* Classes & Exceptions renamed to make use of the namespace
* Most statically called functions now use the `static` keyword instead of `self`, so it's possible to override static internal functions in subclasses, for example `validateAddress()`
* Complete RFC standardisation on CRLF (`\r\n`) line breaks for SMTP by default:
  * `PHPMailer:$LE` defaults to CRLF
  * All uses of `PHPMailer::$LE` property converted to use `static::$LE` constant for consistency and ease of overriding
  * Similar changes to line break handling in SMTP and POP3 classes.
  * Line break format for `mail()` transport is set automatically.
  * Warnings emitted for buggy `mail()` in PHP versions 7.0.0 - 7.0.16 and 7.1.0 - 7.1.2; either upgrade or switch to SMTP.
* Extensive reworking of XOAUTH2, adding support for Google, Yahoo and Microsoft providers, thanks to @sherryl4george
* Major cleanup of docs and examples
* All elements previously marked as deprecated have been removed:
  * `PHPMailer->Version` (replaced with `VERSION` constant)
  * `PHPMailer->ReturnPath`
  * `PHPMailer->PluginDir`
  * `PHPMailer->encodeQPphp()`
  * `SMTP->CRLF` (replaced with `LE` constant)
  * `SMTP->Version` (replaced with `VERSION` constant)
  * `SMTP->SMTP_PORT` (replaced with `DEFAULT_PORT` constant)
  * `POP3->CRLF` (replaced with `LE` constant)
  * `POP3->Version` (replaced with `VERSION` constant)
  * `POP3->POP3_PORT` (replaced with `DEFAULT_PORT` constant)
  * `POP3->POP3_TIMEOUT` (replaced with `DEFAULT_TIMEOUT` constant)
* NTLM authentication has been removed - it never worked anyway!
  * `PHPMailer->Workstation`
  * `PHPMailer->Realm`
* `SingleTo` functionality is deprecated; this belongs at a higher level - PHPMailer is not a mailing list system.
* `SMTP::authenticate` method signature changed
* `parseAddresses()` is now static
* `validateAddress()` is now called statically from `parseAddresses()`
* `idnSupported()` is now static and is called statically from `punyencodeAddress()`
* `PHPMailer->SingleToArray` is now protected
* `fixEOL()` method removed - it duplicates `PHPMailer::normalizeBreaks()`, so use that instead
* Don't try to use an auth mechanism if it's not supported by the server
* Reorder automatic AUTH mechanism selector to try most secure method first
* `Extras` classes have been removed - use alternative packages from [packagist.org](https://packagist.org) instead
* Better handling of automatic transfer encoding switch in the presence of long lines
* Simplification of address validation - now uses PHP's `FILTER_VALIDATE_EMAIL` pattern by default, retains advanced options
* `Debugoutput` can accept a PSR-3 logger instance
* To reduce code footprint, the examples folder is no longer included in composer deployments or github zip files
* Trap low-level errors in SMTP, reports via debug output
* More reliable folding of message headers
* Inject your own SMTP implementation via `setSMTPInstance()` instead of having to subclass and override `getSMTPInstance()`.
* Make obtaining SMTP transaction ID more reliable
* Better handling of unreliable PHP timeouts
* Made `SMTPDebug = 4` slightly less noisy

## Version 5.2.25 (August 28th 2017)
* Make obtaining SMTP transaction ID more reliable
* Add Bosnian translation
* This is the last official release in the legacy PHPMailer 5.2 series; there may be future security patches (which will be found in the [5.2-stable branch](https://github.com/PHPMailer/PHPMailer/tree/5.2-stable)), but no further non-security PRs or issues will be accepted. Migrate to PHPMailer 6.0.

## Version 5.2.24 (July 26th 2017)
* **SECURITY** Fix XSS vulnerability in one of the code examples, [CVE-2017-11503](https://web.nvd.nist.gov/view/vuln/detail?vulnId=CVE-2017-11503). The `code_generator.phps` example did not filter user input prior to output. This file is distributed with a `.phps` extension, so it it not normally executable unless it is explicitly renamed, so it is safe by default. There was also an undisclosed potential XSS vulnerability in the default exception handler (unused by default). Patches for both issues kindly provided by Patrick Monnerat of the Fedora Project.
* Handle bare codes (an RFC contravention) in SMTP server responses
* Make message timestamps more dynamic - calculate the date separately for each message
* More thorough checks for reading attachments.
* Throw an exception when trying to send a message with an empty body caused by an internal error.
* Replaced all use of MD5 and SHA1 hash functions with SHA256.
* Now checks for invalid host strings when sending via SMTP.
* Include timestamps in HTML-format debug output
* Improve Turkish, Norwegian, Serbian, Brazilian Portuguese & simplified Chinese translations
* Correction of Serbian ISO language code from `sr` to `rs`
* Fix matching of multiple entries in `Host` to match IPv6 literals without breaking port selection (see #1094, caused by a3b4f6b)
* Better capture and reporting of SMTP connection errors

## Version 5.2.23 (March 15th 2017)
* Improve trapping of TLS errors during connection so that they don't cause warnings, and are reported better in debug output
* Amend test suite so it uses PHPUnit version 4.8, compatible with older versions of PHP, instead of the version supplied by Travis-CI
* This forces pinning of some dev packages to older releases, but should make travis builds more reliable
* Test suite now runs on HHVM, and thus so should PHPMailer in general
* Improve Czech translations
* Add links to CVE-2017-5223 resources

## Version 5.2.22 (January 5th 2017)
* **SECURITY** Fix [CVE-2017-5223](https://web.nvd.nist.gov/view/vuln/detail?vulnId=CVE-2017-5223), local file disclosure vulnerability if content passed to `msgHTML()` is sourced from unfiltered user input. Reported by Yongxiang Li of Asiasecurity. The fix for this means that calls to `msgHTML()` without a `$basedir` will not import images with relative URLs, and relative URLs containing `..` will be ignored.
* Add simple contact form example
* Emoji in test content

## Version 5.2.21 (December 28th 2016)
* Fix missed number update in version file - no functional changes

## Version 5.2.20 (December 28th 2016)
* **SECURITY** Critical security update for CVE-2016-10045 please update now! Thanks to [Dawid Golunski](https://legalhackers.com) and Paul Buonopane (@Zenexer).
* Note that this change will break VERP addresses in Sender if you're using mail() - workaround: use SMTP to localhost instead.

## Version 5.2.19 (December 26th 2016)
* Minor cleanup

## Version 5.2.18 (December 24th 2016)
* **SECURITY** Critical security update for CVE-2016-10033 please update now! Thanks to [Dawid Golunski](https://legalhackers.com).
* Add ability to extract the SMTP transaction ID from some common SMTP success messages
* Minor documentation tweaks

## Version 5.2.17 (December 9th 2016)
* This is officially the last feature release of 5.2. Security fixes only from now on; use PHPMailer 6.0!
* Allow DKIM private key to be provided as a string
* Provide mechanism to allow overriding of boundary and message ID creation
* Improve Brazilian Portuguese, Spanish, Swedish, Romanian, and German translations
* PHP 7.1 support for Travis-CI
* Fix some language codes
* Add security notices
* Improve DKIM compatibility in older PHP versions
* Improve trapping and capture of SMTP connection errors
* Improve passthrough of error levels for debug output
* PHPDoc cleanup

## Version 5.2.16 (June 6th 2016)
* Added DKIM example
* Fixed empty additional_parameters problem
* Fixed wrong version number in VERSION file!
* Improve line-length tests
* Use instance settings for SMTP::connect by default
* Use more secure auth mechanisms first

## Version 5.2.15 (May 10th 2016)
* Added ability to inject custom address validators, and set the default validator
* Fix TLS 1.2 compatibility
* Remove some excess line breaks in MIME structure
* Updated Polish, Russian, Brazilian Portuguese, Georgian translations
* More DRY!
* Improve error messages
* Update dependencies
* Add example showing how to handle multiple form file uploads
* Improve SMTP example
* Improve Windows compatibility
* Use consistent names for temp files
* Fix gmail XOAUTH2 scope, thanks to @sherryl4george
* Fix extra line break in getSentMIMEMessage()
* Improve DKIM signing to use SHA-2

## Version 5.2.14 (Nov 1st 2015)
* Allow addresses with IDN (Internationalized Domain Name) in PHP 5.3+, thanks to @fbonzon
* Allow access to POP3 errors
* Make all POP3 private properties and methods protected
* **SECURITY** Fix vulnerability that allowed email addresses with line breaks (valid in RFC5322) to pass to SMTP, permitting message injection at the SMTP level. Mitigated in both the address validator and in the lower-level SMTP class. Thanks to Takeshi Terada.
* Updated Brazilian Portuguese translations (Thanks to @phelipealves)

## Version 5.2.13 (Sep 14th 2015)
* Rename internal oauth class to avoid name clashes
* Improve Estonian translations

## Version 5.2.12 (Sep 1st 2015)
* Fix incorrect composer package dependencies
* Skip existing embedded image `cid`s in `msgHTML`

## Version 5.2.11 (Aug 31st 2015)
* Don't switch to quoted-printable for long lines if already using base64
* Fixed Travis-CI config when run on PHP 7
* Added Google XOAUTH2 authentication mechanism, thanks to @sherryl4george
* Add address parser for RFC822-format addresses
* Update MS Office MIME types
* Don't convert line breaks when using quoted-printable encoding
* Handle MS Exchange returning an invalid empty AUTH-type list in EHLO
* Don't set name or filename properties on MIME parts that don't have one

## Version 5.2.10 (May 4th 2015)
* Add custom header getter
* Use `application/javascript` for .js attachments
* Improve RFC2821 compliance for timelimits, especially for end-of-data
* Add Azerbaijani translations (Thanks to @mirjalal)
* Minor code cleanup for robustness
* Add Indonesian translations (Thanks to @ceceprawiro)
* Avoid `error_log` Debugoutput naming clash
* Add ability to parse server capabilities in response to EHLO (useful for SendGrid etc)
* Amended default values for WordWrap to match RFC
* Remove html2text converter class (has incompatible license)
* Provide new mechanism for injecting html to text converters
* Improve pointers to docs and support in README
* Add example file upload script
* Refactor and major cleanup of EasyPeasyICS, now a lot more usable
* Make set() method simpler and more reliable
* Add Malay translation (Thanks to @nawawi)
* Add Bulgarian translation (Thanks to @mialy)
* Add Armenian translation (Thanks to Hrayr Grigoryan)
* Add Slovenian translation (Thanks to Klemen Tušar)
* More efficient word wrapping
* Add support for S/MIME signing with additional CA certificate (thanks to @IgitBuh)
* Fix incorrect MIME structure when using S/MIME signing and isMail() (#372)
* Improved checks and error messages for missing extensions
* Store and report SMTP errors more consistently
* Add MIME multipart preamble for better Outlook compatibility
* Enable TLS encryption automatically if the server offers it
* Provide detailed errors when individual recipients fail
* Report more errors when connecting
* Add extras classes to composer classmap
* Expose stream_context_create options via new SMTPOptions property
* Automatic encoding switch to quoted-printable if message lines are too long
* Add Korean translation (Thanks to @ChalkPE)
* Provide a pointer to troubleshooting docs on SMTP connection failure

## Version 5.2.9 (Sept 25th 2014)
* **Important: The autoloader is no longer autoloaded by the PHPMailer class**
* Update html2text from https://github.com/mtibben/html2text
* Improve Arabic translations (Thanks to @tarekdj)
* Consistent handling of connection variables in SMTP and POP3
* PHPDoc cleanup
* Update composer to use PHPUnit 4.1
* Pass consistent params to callbacks
* More consistent handling of error states and debug output
* Use property defaults, remove constructors
* Remove unreachable code
* Use older regex validation pattern for troublesome PCRE library versions
* Improve PCRE detection in older PHP versions
* Handle debug output consistently, and always in UTF-8
* Allow user-defined debug output method via a callable
* msgHTML now converts data URIs to embedded images
* SMTP::getLastReply() will now always be populated
* Improved example code in README
* Ensure long filenames in Content-Disposition are encoded correctly
* Simplify SMTP debug output mechanism, clarify levels with constants
* Add SMTP connection check example
* Simplify examples, don't use mysql* functions

## Version 5.2.8 (May 14th 2014)
* Increase timeout to match RFC2821 section 4.5.3.2 and thus not fail greetdelays, fixes #104
* Add timestamps to default debug output
* Add connection events and new level 3 to debug output options
* Chinese language update (Thanks to @binaryoung)
* Allow custom Mailer types (Thanks to @michield)
* Cope with spaces around SMTP host specs
* Fix processing of multiple hosts in connect string
* Added Galician translation (Thanks to @donatorouco)
* Autoloader now prepends
* Docs updates
* Add Latvian translation (Thanks to @eddsstudio)
* Add Belarusian translation (Thanks to @amaksymiuk)
* Make autoloader work better on older PHP versions
* Avoid double-encoding if mbstring is overloading mail()
* Add Portuguese translation (Thanks to @Jonadabe)
* Make quoted-printable encoder respect line ending setting
* Improve Chinese translation (Thanks to @PeterDaveHello)
* Add Georgian translation (Thanks to @akalongman)
* Add Greek translation (Thanks to @lenasterg)
* Fix serverHostname on PHP < 5.3
* Improve performance of SMTP class
* Implement automatic 7bit downgrade
* Add Vietnamese translation (Thanks to @vinades)
* Improve example images, switch to PNG
* Add Croatian translation (Thanks to @hrvoj3e)
* Remove setting the Return-Path and deprecate the Return-path property - it's just wrong!
* Fix language file loading if CWD has changed (@stephandesouza)
* Add HTML5 email validation pattern
* Improve Turkish translations (Thanks to @yasinaydin)
* Improve Romanian translations (Thanks to @aflorea)
* Check php.ini for path to sendmail/qmail before using default
* Improve Farsi translation (Thanks to @MHM5000)
* Don't use quoted-printable encoding for multipart types
* Add Serbian translation (Thanks to ajevremovic at gmail.com)
* Remove useless PHP5 check
* Use SVG for build status badges
* Store MessageDate on creation
* Better default behaviour for validateAddress

## Version 5.2.7 (September 12th 2013)
* Add Ukrainian translation from @Krezalis
* Support for do_verp
* Fix bug in CRAM-MD5 AUTH
* Propagate Debugoutput option to SMTP class (@Reblutus)
* Determine MIME type of attachments automatically
* Add cross-platform, multibyte-safe pathinfo replacement (with tests) and use it
* Add a new 'html' Debugoutput type
* Clean up SMTP debug output, remove embedded HTML
* Some small changes in header formatting to improve IETF msglint test results
* Update test_script to use some recently changed features, rename to code_generator
* Generated code actually works!
* Update SyntaxHighlighter
* Major overhaul and cleanup of example code
* New PHPMailer graphic
* msgHTML now uses RFC2392-compliant content ids
* Add line break normalization function and use it in msgHTML
* Don't set unnecessary reply-to addresses
* Make fakesendmail.sh a bit cleaner and safer
* Set a content-transfer-encoding on multiparts (fixes msglint error)
* Fix cid generation in msgHTML (Thanks to @digitalthought)
* Fix handling of multiple SMTP servers (Thanks to @NanoCaiordo)
* SMTP->connect() now supports stream context options (Thanks to @stanislavdavid)
* Add support for iCal event alternatives (Thanks to @reblutus)
* Update to Polish language file (Thanks to Krzysztof Kowalewski)
* Update to Norwegian language file (Thanks to @datagutten)
* Update to Hungarian language file (Thanks to @dominicus-75)
* Add Persian/Farsi translation from @jaii
* Make SMTPDebug property type match type in SMTP class
* Add unit tests for DKIM
* Major refactor of SMTP class
* Reformat to PSR-2 coding standard
* Introduce autoloader
* Allow overriding of SMTP class
* Overhaul of PHPDocs
* Fix broken Q-encoding
* Czech language update (Thanks to @nemelu)
* Removal of excess blank lines in messages
* Added fake POP server and unit tests for POP-before-SMTP

## Version 5.2.6 (April 11th 2013)
* Reflect move to PHPMailer GitHub organisation at https://github.com/PHPMailer/PHPMailer
* Fix unbumped version numbers
* Update packagist.org with new location
* Clean up Changelog

## Version 5.2.5 (April 6th 2013)
* First official release after move from Google Code
* Fixes for qmail when sending via mail()
* Merge in changes from Google code 5.2.4 release
* Minor coding standards cleanup in SMTP class
* Improved unit tests, now tests S/MIME signing
* Travis-CI support on GitHub, runs tests with fake SMTP server

## Version 5.2.4 (February 19, 2013)
* Fix tag and version bug.
* un-deprecate isSMTP(), isMail(), IsSendmail() and isQmail().
* Numerous translation updates

## Version 5.2.3 (February 8, 2013)
* Fix issue with older PCREs and ValidateAddress() (Bugz: 124)
* Add CRAM-MD5 authentication, thanks to Elijah madden, https://github.com/okonomiyaki3000
* Replacement of obsolete Quoted-Printable encoder with a much better implementation
* Composer package definition
* New language added: Hebrew

## Version 5.2.2 (December 3, 2012)
* Some fixes and syncs from https://github.com/Synchro/PHPMailer
* Add Slovak translation, thanks to Michal Tinka

## Version 5.2.2-rc2 (November 6, 2012)
* Fix SMTP server rotation (Bugz: 118)
* Allow override of autogen'ed 'Date' header (for Drupal's
  og_mailinglist module)
* No whitespace after '-f' option (Bugz: 116)
* Work around potential warning (Bugz: 114)

## Version 5.2.2-rc1 (September 28, 2012)
* Header encoding works with long lines (Bugz: 93)
* Turkish language update (Bugz: 94)
* undefined $pattern in EncodeQ bug squashed (Bugz: 98)
* use of mail() in safe_mode now works (Bugz: 96)
* ValidateAddress() now 'public static' so people can override the
  default and use their own validation scheme.
* ValidateAddress() no longer uses broken FILTER_VALIDATE_EMAIL
* Added in AUTH PLAIN SMTP authentication

## Version 5.2.2-beta2 (August 17, 2012)
* Fixed Postfix VERP support (Bugz: 92)
* Allow action_function callbacks to pass/use
  the From address (passed as final param)
* Prevent inf look for get_lines() (Bugz: 77)
* New public var ($UseSendmailOptions). Only pass sendmail()
  options iff we really are using sendmail or something sendmail
  compatible. (Bugz: 75)
* default setting for LE returned to "\n" due to popular demand.

## Version 5.2.2-beta1 (July 13, 2012)
* Expose PreSend() and PostSend() as public methods to allow
  for more control if serializing message sending.
* GetSentMIMEMessage() only constructs the message copy when
 needed. Save memory.
* Only pass params to mail() if the underlying MTA is
  "sendmail" (as defined as "having the string sendmail
  in its pathname") [#69]
* Attachments now work with Amazon SES and others [Bugz#70]
* Debug output now sent to stdout (via echo) or error_log [Bugz#5]
* New var: Debugoutput (for above) [Bugz#5]
* SMTP reads now Timeout aware (new var: Timeout=15) [Bugz#71]
* SMTP reads now can have a Timelimit associated with them
  (new var: Timelimit=30)[Bugz#71]
* Fix quoting issue associated with charsets
* default setting for LE is now RFC compliant: "\r\n"
* Return-Path can now be user defined (new var: ReturnPath)
  (the default is "" which implies no change from previous
  behavior, which was to use either From or Sender) [Bugz#46]
* X-Mailer header can now be disabled (by setting to a
  whitespace string, eg "  ") [Bugz#66]
* Bugz closed: #68, #60, #42, #43, #59, #55, #66, #48, #49,
               #52, #31, #41, #5. #70, #69

## Version 5.2.1 (January 16, 2012)
* Closed several bugs #5
* Performance improvements
* MsgHTML() now returns the message as required.
* New method: GetSentMIMEMessage() (returns full copy of sent message)

## Version 5.2 (July 19, 2011)
* protected MIME body and header
* better DKIM DNS Resource Record support
* better aly handling
* htmlfilter class added to extras
* moved to Apache Extras

## Version 5.1 (October 20, 2009)
* fixed filename issue with AddStringAttachment (thanks to Tony)
* fixed "SingleTo" property, now works with Senmail, Qmail, and SMTP in
  addition to PHP mail()
* added DKIM digital signing functionality, new properties:
  - DKIM_domain (sets the domain name)
  - DKIM_private (holds DKIM private key)
  - DKIM_passphrase (holds your DKIM passphrase)
  - DKIM_selector (holds the DKIM "selector")
  - DKIM_identity (holds the identifying email address)
* added callback function support
  - callback function parameters include:
    result, to, cc, bcc, subject and body
  - see the test/test_callback.php file for usage.
* added "auto" identity functionality
  - can automatically add:
    - Return-path (if Sender not set)
    - Reply-To (if ReplyTo not set)
  - can be disabled:
    - $mail->SetFrom('yourname@yourdomain.com','First Last',false);
    - or by adding the $mail->Sender and/or $mail->ReplyTo properties

Note: "auto" identity added to help with emails ending up in spam or junk boxes because of missing headers

## Version 5.0.2 (May 24, 2009)
* Fix for missing attachments when inline graphics are present
* Fix for missing Cc in header when using SMTP (mail was sent,
  but not displayed in header -- Cc receiver only saw email To:
  line and no Cc line, but did get the email (To receiver
  saw same)

## Version 5.0.1 (April 05, 2009)
* Temporary fix for missing attachments

## Version 5.0.0 (April 02, 2009)
With the release of this version, we are initiating a new version numbering
system to differentiate from the PHP4 version of PHPMailer.
Most notable in this release is fully object oriented code.

### class.smtp.php:
* Refactored class.smtp.php to support new exception handling
* code size reduced from 29.2 Kb to 25.6 Kb
* Removed unnecessary functions from class.smtp.php:
  - public function Expand($name) {
  - public function Help($keyword="") {
  - public function Noop() {
  - public function Send($from) {
  - public function SendOrMail($from) {
  - public function Verify($name) {

###  class.phpmailer.php:
* Refactored class.phpmailer.php with new exception handling
* Changed processing functionality of Sendmail and Qmail so they cannot be
  inadvertently used
* removed getFile() function, just became a simple wrapper for
  file_get_contents()
* added check for PHP version (will gracefully exit if not at least PHP 5.0)
* enhanced code to check if an attachment source is the same as an embedded or
  inline graphic source to eliminate duplicate attachments

### New /test_script
We have written a test script you can use to test the script as part of your
installation. Once you press submit, the test script will send a multi-mime
email with either the message you type in or an HTML email with an inline
graphic. Two attachments are included in the email (one of the attachments
is also the inline graphic so you can see that only one copy of the graphic
is sent in the email). The test script will also display the functional
script that you can copy/paste to your editor to duplicate the functionality.

### New examples
All new examples in both basic and advanced modes. Advanced examples show
   Exception handling.

### PHPDocumentator (phpdocs) documentation for PHPMailer version 5.0.0
All new documentation

## Version 2.3 (November 06, 2008)
* added Arabic language (many thanks to Bahjat Al Mostafa)
* removed English language from language files and made it a default within
  class.phpmailer.php - if no language is found, it will default to use
  the english language translation
* fixed public/private declarations
* corrected line 1728, $basedir to $directory
* added $sign_cert_file to avoid improper duplicate use of $sign_key_file
* corrected $this->Hello on line 612 to $this->Helo
* changed default of $LE to "\r\n" to comply with RFC 2822. Can be set by the user
  if default is not acceptable
* removed trim() from return results in EncodeQP
* /test and three files it contained are removed from version 2.3
* fixed phpunit.php for compliance with PHP5
* changed $this->AltBody = $textMsg; to $this->AltBody = html_entity_decode($textMsg);
* We have removed the /phpdoc from the downloads. All documentation is now on
  the http://phpmailer.codeworxtech.com website.

## Version 2.2.1 () July 19 2008
* fixed line 1092 in class.smtp.php (my apologies, error on my part)

## Version 2.2 () July 15 2008
* Fixed redirect issue (display of UTF-8 in thank you redirect)
* fixed error in getResponse function declaration (class.pop3.php)
* PHPMailer now PHP6 compliant
* fixed line 1092 in class.smtp.php (endless loop from missing = sign)

## Version 2.1 (Wed, June 04 2008)
NOTE: WE HAVE A NEW LANGUAGE VARIABLE FOR DIGITALLY SIGNED S/MIME EMAILS. IF YOU CAN HELP WITH LANGUAGES OTHER THAN ENGLISH AND SPANISH, IT WOULD BE APPRECIATED.

* added S/MIME functionality (ability to digitally sign emails)
  BIG THANKS TO "sergiocambra" for posting this patch back in November 2007.
  The "Signed Emails" functionality adds the Sign method to pass the private key
  filename and the password to read it, and then email will be sent with
  content-type multipart/signed and with the digital signature attached.
* fully compatible with E_STRICT error level
  - Please note:
    In about half the test environments this development version was subjected
    to, an error was thrown for the date() functions used (line 1565 and 1569).
    This is NOT a PHPMailer error, it is the result of an incorrectly configured
    PHP5 installation. The fix is to modify your 'php.ini' file and include the
    date.timezone = Etc/UTC (or your own zone)
    directive, to your own server timezone
  - If you do get this error, and are unable to access your php.ini file:
    In your PHP script, add
    `date_default_timezone_set('Etc/UTC');`
  - do not try to use
    `$myVar = date_default_timezone_get();`
    as a test, it will throw an error.
* added ability to define path (mainly for embedded images)
  function `MsgHTML($message,$basedir='')` ... where:
  `$basedir` is the fully qualified path
* fixed `MsgHTML()` function:
  - Embedded Images where images are specified by `<protocol>://` will not be altered or embedded
* fixed the return value of SMTP exit code ( pclose )
* addressed issue of multibyte characters in subject line and truncating
* added ability to have user specified Message ID
  (default is still that PHPMailer create a unique Message ID)
* corrected unidentified message type to 'application/octet-stream'
* fixed chunk_split() multibyte issue (thanks to Colin Brown, et al).
* added check for added attachments
* enhanced conversion of HTML to text in MsgHTML (thanks to "brunny")

## Version 2.1.0beta2 (Sun, Dec 02 2007)
* implemented updated EncodeQP (thanks to coolbru, aka Marcus Bointon)
* finished all testing, all known bugs corrected, enhancements tested

Note: will NOT work with PHP4.

Please note, this is BETA software **DO NOT USE THIS IN PRODUCTION OR LIVE PROJECTS; INTENDED STRICTLY FOR TESTING**

## Version 2.1.0beta1
Please note, this is BETA software
** DO NOT USE THIS IN PRODUCTION OR LIVE PROJECTS
 INTENDED STRICTLY FOR TESTING

## Version 2.0.0 rc2 (Fri, Nov 16 2007), interim release
* implements new property to control VERP in class.smtp.php
  example (requires instantiating class.smtp.php):
  $mail->do_verp = true;
* POP-before-SMTP functionality included, thanks to Richard Davey
  (see class.pop3.php & pop3_before_smtp_test.php for examples)
* included example showing how to use PHPMailer with GMAIL
* fixed the missing Cc in SendMail() and Mail()

## Version 2.0.0 rc1 (Thu, Nov 08 2007), interim release
* dramatically simplified using inline graphics ... it's fully automated and requires no user input
* added automatic document type detection for attachments and pictures
* added MsgHTML() function to replace Body tag for HTML emails
* fixed the SendMail security issues (input validation vulnerability)
* enhanced the AddAddresses functionality so that the "Name" portion is used in the email address
* removed the need to use the AltBody method (set from the HTML, or default text used)
* set the PHP Mail() function as the default (still support SendMail, SMTP Mail)
* removed the need to set the IsHTML property (set automatically)
* added Estonian language file by Indrek P&auml;ri
* added header injection patch
* added "set" method to permit users to create their own pseudo-properties like 'X-Headers', etc.
* fixed warning message in SMTP get_lines method
* added TLS/SSL SMTP support.
* PHPMailer has been tested with PHP4 (4.4.7) and PHP5 (5.2.7)
* Works with PHP installed as a module or as CGI-PHP
NOTE: will NOT work with PHP5 in E_STRICT error mode

## Version 1.73 (Sun, Jun 10 2005)
* Fixed denial of service bug: http://www.cybsec.com/vuln/PHPMailer-DOS.pdf
* Now has a total of 20 translations
* Fixed alt attachments bug: http://tinyurl.com/98u9k

## Version 1.72 (Wed, May 25 2004)
* Added Dutch, Swedish, Czech, Norwegian, and Turkish translations.
* Received: Removed this method because spam filter programs like
  SpamAssassin reject this header.
* Fixed error count bug.
* SetLanguage default is now "language/".
* Fixed magic_quotes_runtime bug.

## Version 1.71 (Tue, Jul 28 2003)
* Made several speed enhancements
* Added German and Italian translation files
* Fixed HELO/AUTH bugs on keep-alive connects
* Now provides an error message if language file does not load
* Fixed attachment EOL bug
* Updated some unclear documentation
* Added additional tests and improved others

## Version 1.70 (Mon, Jun 20 2003)
* Added SMTP keep-alive support
* Added IsError method for error detection
* Added error message translation support (SetLanguage)
* Refactored many methods to increase library performance
* Hello now sends the newer EHLO message before HELO as per RFC 2821
* Removed the boundary class and replaced it with GetBoundary
* Removed queue support methods
* New $Hostname variable
* New Message-ID header
* Received header reformat
* Helo variable default changed to $Hostname
* Removed extra spaces in Content-Type definition (#667182)
* Return-Path should be set to Sender when set
* Adds Q or B encoding to headers when necessary
* quoted-encoding should now encode NULs \000
* Fixed encoding of body/AltBody (#553370)
* Adds "To: undisclosed-recipients:;" when all recipients are hidden (BCC)
* Multiple bug fixes

## Version 1.65 (Fri, Aug 09 2002)
* Fixed non-visible attachment bug (#585097) for Outlook
* SMTP connections are now closed after each transaction
* Fixed SMTP::Expand return value
* Converted SMTP class documentation to phpDocumentor format

## Version 1.62 (Wed, Jun 26 2002)
* Fixed multi-attach bug
* Set proper word wrapping
* Reduced memory use with attachments
* Added more debugging
* Changed documentation to phpDocumentor format

## Version 1.60 (Sat, Mar 30 2002)
* Sendmail pipe and address patch (Christian Holtje)
* Added embedded image and read confirmation support (A. Ognio)
* Added unit tests
* Added SMTP timeout support (*nix only)
* Added possibly temporary PluginDir variable for SMTP class
* Added LE message line ending variable
* Refactored boundary and attachment code
* Eliminated SMTP class warnings
* Added SendToQueue method for future queuing support

## Version 1.54 (Wed, Dec 19 2001)
* Add some queuing support code
* Fixed a pesky multi/alt bug
* Messages are no longer forced to have "To" addresses

## Version 1.50 (Thu, Nov 08 2001)
* Fix extra lines when not using SMTP mailer
* Set WordWrap variable to int with a zero default

## Version 1.47 (Tue, Oct 16 2001)
* Fixed Received header code format
* Fixed AltBody order error
* Fixed alternate port warning

## Version 1.45 (Tue, Sep 25 2001)
* Added enhanced SMTP debug support
* Added support for multiple ports on SMTP
* Added Received header for tracing
* Fixed AddStringAttachment encoding
* Fixed possible header name quote bug
* Fixed wordwrap() trim bug
* Couple other small bug fixes

## Version 1.41 (Wed, Aug 22 2001)
* Fixed AltBody bug w/o attachments
* Fixed rfc_date() for certain mail servers

## Version 1.40 (Sun, Aug 12 2001)
* Added multipart/alternative support (AltBody)
* Documentation update
* Fixed bug in Mercury MTA

## Version 1.29 (Fri, Aug 03 2001)
* Added AddStringAttachment() method
* Added SMTP authentication support

## Version 1.28 (Mon, Jul 30 2001)
* Fixed a typo in SMTP class
* Fixed header issue with Imail (win32) SMTP server
* Made fopen() calls for attachments use "rb" to fix win32 error

## Version 1.25 (Mon, Jul 02 2001)
* Added RFC 822 date fix (Patrice)
* Added improved error handling by adding a $ErrorInfo variable
* Removed MailerDebug variable (obsolete with new error handler)

## Version 1.20 (Mon, Jun 25 2001)
* Added quoted-printable encoding (Patrice)
* Set Version as public and removed PrintVersion()
* Changed phpdoc to only display public variables and methods

## Version 1.19 (Thu, Jun 21 2001)
* Fixed MS Mail header bug
* Added fix for Bcc problem with mail(). *Does not work on Win32*
  (See PHP bug report: http://www.php.net/bugs.php?id=11616)
* mail() no longer passes a fifth parameter when not needed

## Version 1.15 (Fri, Jun 15 2001)
Note: these changes contributed by Patrice Fournier
* Changed all remaining \n to \r\n
* Bcc: header no longer written to message except
  when sent directly to sendmail
* Added a small message to non-MIME compliant mail reader
* Added Sender variable to change the Sender email
  used in -f for sendmail/mail and in 'MAIL FROM' for smtp mode
* Changed boundary setting to a place it will be set only once
* Removed transfer encoding for whole message when using multipart
* Message body now uses Encoding in multipart messages
* Can set encoding and type to attachments 7bit, 8bit
  and binary attachment are sent as is, base64 are encoded
* Can set Encoding to base64 to send 8 bits body
  through 7 bits servers

## Version 1.10 (Tue, Jun 12 2001)
* Fixed win32 mail header bug (printed out headers in message body)

## Version 1.09 (Fri, Jun 08 2001)
* Changed date header to work with Netscape mail programs
* Altered phpdoc documentation

## Version 1.08 (Tue, Jun 05 2001)
* Added enhanced error-checking
* Added phpdoc documentation to source

## Version 1.06 (Fri, Jun 01 2001)
* Added optional name for file attachments

## Version 1.05 (Tue, May 29 2001)
* Code cleanup
* Eliminated sendmail header warning message
* Fixed possible SMTP error

## Version 1.03 (Thu, May 24 2001)
* Fixed problem where qmail sends out duplicate messages

## Version 1.02 (Wed, May 23 2001)
* Added multiple recipient and attachment Clear* methods
* Added Sendmail public variable
* Fixed problem with loading SMTP library multiple times

## Version 0.98 (Tue, May 22 2001)
* Fixed problem with redundant mail hosts sending out multiple messages
* Added additional error handler code
* Added AddCustomHeader() function
* Added support for Microsoft mail client headers (affects priority)
* Fixed small bug with Mailer variable
* Added PrintVersion() function

## Version 0.92 (Tue, May 15 2001)
* Changed file names to class.phpmailer.php and class.smtp.php to match
  current PHP class trend.
* Fixed problem where body not being printed when a message is attached
* Several small bug fixes

## Version 0.90 (Tue, April 17 2001)
* Initial public release
