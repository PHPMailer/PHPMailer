<?php

namespace PHPMailer\PHPMailer;

use Aws\Ses\SesClient;
use Aws\Ses\Exception\SesException;
use Aws\Credentials\Credentials;

class SES
{
    /**
     * AWS SES Client
     * @var SesClient|null
     */
    protected $sesClient = null;

    /**
     * The last transaction ID issued in response to a sendRawEmail command
     *
     * @var string|bool|null
     */
    protected $last_transaction_id;

    /**
     * AWS region to use
     * @var string
     */
    protected $region = 'eu-west-1';

    /**
     * @var Credentials||null
     */
    protected $credentials = null;

    /**
     * @param bool|Credentials $credentials
     * @return void
     */
    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * Sets the AWS region we want to use
     * @param string $region
     * @return void
     */
    public function setRegion($region)
    {
        $this->region = $region;
    }

    public function initSesClient()
    {
        $options = [
            'region' => $this->region,
            'version' => 'latest'
        ];

        if (!is_null($this->credentials)) {
            $options += [
                'credentials' => $this->credentials
            ];
        }

        $this->sesClient = new SesClient($options);

        return $this->sesClient;
    }

    public function sendRaw($message)
    {
        try {
            if (is_null($this->sesClient)) {
                $this->initSesClient();
            }

            $result = $this->sesClient->sendRawEmail([
                'RawMessage' => [
                    'Data' => $message
                ]
            ]);

            // If the message was sent, show the message ID.
            $this->last_transaction_id = $result->get('MessageId');
        } catch (Exception $error) {
            return false;
        }

        return true;
    }

    /**
     * Get the queue/transaction ID of the last SMTP transaction
     * If no reply has been received yet, it will return null.
     * If no pattern was matched, it will return false.
     *
     * @return bool|string|null
     *
     * @see recordLastTransactionID()
     */
    public function getLastTransactionID()
    {
        return $this->last_transaction_id;
    }
}