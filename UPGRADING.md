# Upgrading from PHPMailer 5.2 to 6.0

PHPMailer 6.0 is a major update, breaking backward compatibility.

If you're in doubt about how you should be using PHPMailer 6, take a look at the examples as they have all been updated to work in a PHPMailer 6.0 style.

## PHP Version

PHPMailer 6.0 requires PHP 5.5 or later, and is fully compatible with PHP versions all the way up to 8.4. PHPMailer 5.2 supported PHP 5.0 and upwards, so if you need to run on a legacy PHP version, see the [PHPMailer 5.2-stable branch on Github](https://github.com/PHPMailer/PHPMailer/tree/5.2-stable), but bear in mind that this branch is no longer maintained.

## Loading PHPMailer
 
The single biggest change will be in the way that you load PHPMailer. In earlier versions you may have done this:

```php
require 'PHPMailerAutoload.php';
```

or

```php
require 'class.phpmailer.php';
require 'class.smtp.php';
```

We recommend that you load PHPMailer via composer, using its standard autoloader, which you probably won't need to load if you're using it already, but in case you're not, you will need to do this instead:

```php
require 'vendor/autoload.php';
```

If you're not using composer, you can still load the classes manually, depending on what you're using:

```php
require 'src/PHPMailer.php';
require 'src/SMTP.php';
require 'src/Exception.php';
```

## Namespace
PHPMailer 6 uses a [namespace](https://www.php.net/manual/en/language.namespaces.rationale.php) of `PHPMailer\PHPMailer`, because it's the PHPMailer project within the PHPMailer organisation. You **must** import (with a `use` statement) classes you're using explicitly into your own namespace, or reference them absolutely in the global namespace - all the examples do this. This means the fully-qualified name of the main PHPMailer class is `PHPMailer\PHPMailer\PHPMailer`, which is a bit of a mouthful, but there's no harm in it! If you are using other PHPMailer classes explicitly (such as `SMTP` or `Exception`), you will need to import them into your namespace too.

For example you might create an instance like this:

```php
<?php
namespace MyProject;
use PHPMailer\PHPMailer\PHPMailer;
require 'vendor/autoload.php';
$mail = new PHPMailer;
...
```

or alternatively, using a fully qualified name:

```php
<?php
namespace MyProject;
require 'vendor/autoload.php';
$mail = new PHPMailer\PHPMailer\PHPMailer;
...
```

Note that `use` statements apply *only* to the file they appear in (they are local aliases), so if an included file contains `use` statements, it will not import the namespaced classes into the file you're including from.

## Namespaced exceptions
PHPMailer now uses its own namespaced `Exception` class, so if you were previously catching exceptions of type `phpmailerException` (or subclasses of that), you will need to update them to use the PHPMailer namespace, and make any existing `Exception` references use the global namespace, i.e. `\Exception`. If your original code was:

```php
try {
...
} catch (phpmailerException $e) {
    echo $e->errorMessage();
} catch (Exception $e) {
    echo $e->getMessage();
}
```

Convert it to:

```php
use PHPMailer\PHPMailer\Exception;
...
try {
...
} catch (Exception $e) {
    echo $e->errorMessage();
} catch (\Exception $e) {
    echo $e->getMessage();
}
```

## OAuth2 Support
The OAuth2 implementation has been completely redesigned using the [OAuth2 packages](https://oauth2-client.thephpleague.com) from the [League of extraordinary packages](https://thephpleague.com), providing support for many more OAuth services, and you'll need to update your code if you were using OAuth in 5.2. See [the examples](https://github.com/PHPMailer/PHPMailer/tree/master/examples) and documentation in the [PHPMailer wiki](https://github.com/PHPMailer/PHPMailer/wiki).

## Extras
Additional classes previously bundled in the `Extras` folder (such as htmlfilter and EasyPeasyICS) have been removed - use equivalent packages from [packagist.org](https://packagist.org) instead.

## Other upgrade changes
See the changelog for full details.
* File structure simplified, classes live in the `src/` folder
* Most statically called functions now use the `static` keyword instead of `self`, so it's possible to override static internal functions in subclasses, for example `validateAddress()`
* Complete RFC standardisation on CRLF (`\r\n`) line breaks by default:
  * `PHPMailer::$LE` still exists, but all uses of it are changed to `static::$LE` for easier overriding. It may be changed to `\n` automatically when sending via `mail()` on UNIX-like OSs
  * `PHPMailer::CRLF` line ending constant removed
  * The length of the line break is no longer used in line length calculations
  * Similar changes to line break handling in SMTP and POP3 classes
* All elements previously marked as deprecated have been removed:
  * `PHPMailer->Version`
  * `PHPMailer->ReturnPath`
  * `PHPMailer->PluginDir`
  * `PHPMailer->encodeQPphp()`
  * `SMTP->CRLF`
  * `SMTP->Version`
  * `SMTP->SMTP_PORT`
  * `POP3->CRLF`
  * `POP3->Version`
* NTLM authentication has been removed - it never worked anyway!
  * `PHPMailer->Workstation`
  * `PHPMailer->Realm`
* `SMTP::authenticate` method signature changed
* `parseAddresses()` is now static
* `validateAddress()` is now called statically from `parseAddresses()`
* `idnSupported()` is now static and is called statically from `punyencodeAddress()`
* `PHPMailer->SingleToArray` is now protected
