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
     * Boolean options that should be cast to bool
     */
    private const BOOLEAN_OPTIONS = [
        'AllowEmpty',
        'SMTPAutoTLS',
        'SMTPKeepAlive',
        'SingleTo',
        'UseSendmailOptions',
        'do_verp',
        'DKIM_copyHeaderFields'
    ];

    /**
     * Integer options that should be cast to int
     */
    private const INTEGER_OPTIONS = ['Priority', 'SMTPDebug', 'WordWrap'];

    /**
     * Create new PHPMailer instance configured by DSN.
     *
     * @param string $dsn DSN string
     * @param bool|null $exceptions Should we throw external exceptions?
     * @return PHPMailer
     */
    public static function mailer(string $dsn, ?bool $exceptions = null): PHPMailer
    {
        return (new self())->configure(new PHPMailer($exceptions), $dsn);
    }

    /**
     * Configure PHPMailer instance with DSN string.
     *
     * @param PHPMailer $mailer PHPMailer instance
     * @param string $dsn DSN string
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
     * @param string $dsn DSN string
     * @throws Exception If DSN is malformed
     * @return array Configuration
     */
    private function parseDSN(string $dsn): array
    {
        $config = $this->parseUrl($dsn);

        if ($config === false || !isset($config['scheme'], $config['host'])) {
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
     * @param array $config Configuration
     * @throws Exception If scheme is invalid
     */
    private function applyConfig(PHPMailer $mailer, array $config): void
    {
        $this->configureMailerType($mailer, $config['scheme']);

        if ($config['scheme'] === 'smtp' || $config['scheme'] === 'smtps') {
            $this->configureSMTP($mailer, $config);
        }

        if (isset($config['query'])) {
            $this->configureOptions($mailer, $config['query']);
        }
    }

    /**
     * Configure mailer type based on scheme.
     *
     * @param PHPMailer $mailer
     * @param string $scheme
     * @throws Exception
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
            case 'mail': $mailer->isMail(); break;
            case 'sendmail': $mailer->isSendmail(); break;
            case 'qmail': $mailer->isQmail(); break;
            case 'smtp':
            case 'smtps': $mailer->isSMTP(); break;
        }
    }

    /**
     * Configure SMTP settings.
     *
     * @param PHPMailer $mailer
     * @param array $config
     */
    private function configureSMTP(PHPMailer $mailer, array $config): void
    {
        $isSMTPS = $config['scheme'] === 'smtps';

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
     * Configure additional options.
     *
     * @param PHPMailer $mailer
     * @param array $options
     * @throws Exception
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
     * Set option value with proper type casting.
     *
     * @param PHPMailer $mailer
     * @param string $key
     * @param mixed $value
     */
    private function setOptionValue(PHPMailer $mailer, string $key, $value): void
    {
        if (in_array($key, self::BOOLEAN_OPTIONS, true)) {
            $mailer->$key = (bool)$value;
        } elseif (in_array($key, self::INTEGER_OPTIONS, true)) {
            $mailer->$key = (int)$value;
        } else {
            $mailer->$key = $value;
        }
    }

    /**
     * Parse a URL with PHP 5.5 compatibility.
     *
     * @param string $url
     * @return array|false
     */
    protected function parseUrl(string $url)
    {
        if (PHP_VERSION_ID >= 50600 || strpos($url, '?') === false) {
            return parse_url($url);
        }

        $chunks = explode('?', $url, 2);
        $result = parse_url($chunks[0]);
        
        if (is_array($result)) {
            $result['query'] = $chunks[1] ?? '';
        }
        
        return $result;
    }
}
