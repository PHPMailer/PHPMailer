<?php
/**
 * package.xml generation script
 *
 * Usage:
 *
 * $ php generate_package_xml.php make
 * $ pear package package.xml
 *
 * This creates a new package, which can be installed with the following:
 *
 * $ pear install package-version.tgz
 *
 * @package phpmailer
 * @version @package-version@
 * @author  Lars Olesen <lsolesen@users.sourceforge.net>
 * @since   1.73.0
 * @license LGPL License
 */

$version = '1.73.0'; // remember that pear packages requires three numbers
$notes   = 'Initial release as a PEAR package';

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);
$pfm = new PEAR_PackageFileManager2();
$pfm->setOptions(array(
        'baseinstalldir'    => 'phpmailer',
        'filelistgenerator' => 'file',
        'packagedirectory'  => dirname(__FILE__),
        'packagefile'       => 'package.xml',
        'ignore'            => array(
		    'generate_package_xml.php',
            'package.xml',
        	'*.tgz'
            ),
        'dir_roles' => array(
            'docs'     => 'doc',
            'language' => 'php',
            'test'     => 'test'
            ),
        'exceptions' => array(
            'LICENSE'       => 'doc',
            'README'        => 'doc',
            'ChangeLog.txt' => 'doc'
            ),
        'simpleoutput' => true,
));

$pfm->setPackage('phpmailer');
$pfm->setSummary('PHP api for sending e-mails.');
$pfm->setDescription('
* Can send emails with multiple TOs, CCs, BCCs and REPLY-TOs
* Redundant SMTP servers
* Multipart/alternative emails for mail clients that do not read HTML email
* Support for 8bit, base64, binary, and quoted-printable encoding
* Uses the same methods as the very popular AspEmail active server (COM) component
* SMTP authentication
* Word wrap
* Address reset functions
* HTML email
* Tested on multiple SMTP servers: Sendmail, qmail, Postfix, Imail, Exchange, etc
* Works on any platform
* Flexible debugging
* Custom mail headers
* Multiple fs, string, and binary attachments (those from database, string, etc)
* Embedded image support
');
$pfm->setUri('http://localhost/phpmailer-' . $version . '.tgz');
$pfm->setLicense('LGPL License', 'http://www.gnu.org/licenses/lgpl.html');
$pfm->addMaintainer('lead', 'bmatzelle', 'Brent Matzelle', 'bmatzelle@users.sourceforge.net');
$pfm->addMaintainer('developer', 'pfournier', 'Patrice Fournier', 'pfournier@users.sourceforge.net');
$pfm->addMaintainer('contributor', 'lsolesen', 'Lars Olesen', 'lsolesen@users.sourceforge.net');

$pfm->setPackageType('php');

$pfm->setAPIVersion($version);
$pfm->setReleaseVersion($version);
$pfm->setAPIStability('stable');
$pfm->setReleaseStability('stable');
$pfm->setNotes($notes);
$pfm->addRelease();

$pfm->addGlobalReplacement('package-info', '@package-version@', 'version');

$pfm->clearDeps();
$pfm->setPhpDep('4.3.0');
$pfm->setPearinstallerDep('1.5.0');

$pfm->generateContents();

if (isset($_GET['make']) || (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make')) {
    if (!$pfm->writePackageFile()) {
        exit('Error creating package file');
    }
    exit('Package file created');
} else {
    $pfm->debugPackageFile();
}
?>