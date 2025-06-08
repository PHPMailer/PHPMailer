<?php

/**
 * PHPMailer - PHP email creation and transport class.
 * PHP Version 5.5.
 *
 * @see https://github.com/PHPMailer/PHPMailer/ The PHPMailer GitHub project
 *
 * @author    Marcus Bointon (Synchro/coolbru) <phpmailer@synchromedia.co.uk>
 * @author    Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author    Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 * @author    Brent R. Matzelle (original founder)
 * @copyright 2012 - 2023 Marcus Bointon
 * @copyright 2010 - 2012 Jim Jagielski
 * @copyright 2004 - 2009 Andy Prevost
 * @license   https://www.gnu.org/licenses/old-licenses/lgpl-2.1.html GNU Lesser General Public License
 * @note      This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace PHPMailer\PHPMailer;

/**
 * Configure PHPMailer with DSN string.
 *
 * @see https://en.wikipedia.org/wiki/Data_source_name
 *
 * @author Oleg Voronkovich <oleg-voronkovich@yandex.ru>
 */
class DSNConfigurator
{
    /**
     * Allowed mailer schemes
     */
    private const ALLOWED_SCHEMES = ['mail', 'sendmail', 'qmail', 'smtp', 'smtps'];

    /**
     * Create new PHPMailer instance configured by DSN.
     *
     * @param string $dsn        DSN
     * @param bool   $exceptions Should we throw external exceptions?
     *
     * @return PHPMailer
     */
    public static function mailer($dsn, $exceptions = null): PHPMailer
    {
        return (new self())->configure(new PHPMailer($exceptions), $dsn);
    }

    /**
     * Configure PHPMailer instance with DSN string.
     *
     * @param PHPMailer $mailer PHPMailer instance
     * @param string    $dsn    DSN
     *
     * @return PHPMailer
     */
    public function configure(PHPMailer $mailer, string $dsn): PHPMailer
    {
        $config = $this->parseDSN($dsn);
        $this->applyConfig($mailer, $config);

        return $mailer;
    }

    /**
     * Parse DSN string.
     *
     * @param string $dsn DSN
     *
     * @throws Exception If DSN is malformed
     *
     * @return array Configuration
     */
    private function parseDSN(string $dsn): array
    {
        $config = $this->parseUrl($dsn);

        if (false === $config || !isset($config['scheme'], $config['host'])) {
            throw new Exception('Malformed DSN');
        }

        if (isset($config['query'])) {
            parse_str($config['query'], $config['query']);
        }

        return $config;
    }

    /**
     * Apply configuration to mailer.
     *
     * @param PHPMailer $mailer PHPMailer instance
     * @param array     $config Configuration
     *
     * @throws Exception If scheme is invalid
     */
    private function applyConfig(PHPMailer $mailer, array $config): void
    {
        $this->configureMailerType($mailer, $config['scheme']);

        if ('smtp' === $config['scheme'] || 'smtps' === $config['scheme']) {
            $this->configureSMTP($mailer, $config);
        }

        if (isset($config['query'])) {
            $this->configureOptions($mailer, $config['query']);
        }
    }

    /**
     * Configure mailer type based on scheme
     *
     * @param PHPMailer $mailer PHPMailer instance
     * @param string    $scheme Mailer scheme
     *
     * @throws Exception If scheme is invalid
     */
    private function configureMailerType(PHPMailer $mailer, string $scheme): void
    {
        if (!in_array($scheme, self::ALLOWED_SCHEMES, true)) {
            throw new Exception(sprintf(
                'Invalid scheme: "%s". Allowed values: "%s".',
                $scheme,
                implode('", "', self::ALLOWED_SCHEMES)
            ));
        }

        switch ($scheme) {
            case 'mail':
                $mailer->isMail();
                break;
            case 'sendmail':
                $mailer->isSendmail();
                break;
            case 'qmail':
                $mailer->isQmail();
                break;
            case 'smtp':
            case 'smtps':
                $mailer->isSMTP();
                break;
        }
    }

    /**
     * Configure SMTP settings
     *
     * @param PHPMailer $mailer PHPMailer instance
     * @param array     $config Configuration
     */
    private function configureSMTP(PHPMailer $mailer, array $config): void
    {
        $isSMTPS = 'smtps' === $config['scheme'];

        if ($isSMTPS) {
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        $mailer->Host = $config['host'];
        $mailer->Port = $config['port'] ?? ($isSMTPS ? SMTP::DEFAULT_SECURE_PORT : SMTP::DEFAULT_PORT);
        $mailer->SMTPAuth = isset($config['user']) || isset($config['pass']);

        if (isset($config['user'])) {
            $mailer->Username = $config['user'];
        }

        if (isset($config['pass'])) {
            $mailer->Password = $config['pass'];
        }
    }

    /**
     * Configure PHPMailer options
     *
     * @param PHPMailer $mailer  PHPMailer instance
     * @param array     $options Options
     *
     * @throws Exception If option is unknown
     */
    private function configureOptions(PHPMailer $mailer, array $options): void
    {
        $allowedOptions = array_diff_key(
            get_object_vars($mailer),
            array_flip(['Mailer', 'SMTPAuth', 'Username', 'Password', 'Hostname', 'Port', 'ErrorInfo'])
        );

        foreach ($options as $key => $value) {
            if (!array_key_exists($key, $allowedOptions)) {
                throw new Exception(sprintf(
                    'Unknown option: "%s". Allowed values: "%s"',
                    $key,
                    implode('", "', array_keys($allowedOptions))
                );
            }

            $this->setOptionValue($mailer, $key, $value);
        }
    }

    /**
     * Set option value with proper type casting
     *
     * @param PHPMailer $mailer PHPMailer instance
     * @param string    $key    Option name
     * @param mixed     $value  Option value
     */
    private function setOptionValue(PHPMailer $mailer, string $key, $value): void
    {
        $booleanOptions = [
            'AllowEmpty', 'SMTPAutoTLS', 'SMTPKeepAlive', 
            'SingleTo', 'UseSendmailOptions', 'do_verp', 
            'DKIM_copyHeaderFields'
        ];

        $integerOptions = ['Priority', 'SMTPDebug', 'WordWrap'];

        if (in_array($key, $booleanOptions, true)) {
            $mailer->$key = (bool)$value;
        } elseif (in_array($key, $integerOptions, true)) {
            $mailer->$key = (int)$value;
        } else {
            $mailer->$key = $value;
        }
    }

    /**
     * Parse a URL.
     * Wrapper for the built-in parse_url function to work around a bug in PHP 5.5.
     *
     * @param string $url URL
     *
     * @return array|false
     */
    protected function parseUrl(string $url)
    {
        if (\PHP_VERSION_ID >= 50600 || false === strpos($url, '?')) {
            return parse_url($url);
        }

        $chunks = explode('?', $url);
        if (!is_array($chunks)) {
            return false;
        }

        $result = parse_url($chunks[0]);
        if (is_array($result)) {
            $result['query'] = $chunks[1];
        }

        return $result;
    }
}
