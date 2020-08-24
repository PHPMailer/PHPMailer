<?php

if (!function_exists('idn_to_ascii')) {
    function idn_to_ascii()
    {
        return true;
    }
}

if (!function_exists('mb_convert_encoding')) {
    function mb_convert_encoding()
    {
        return true;
    }
}

if (!function_exists('imap_rfc822_parse_adrlist')) {
    function imap_rfc822_parse_adrlist($addressList)
    {
        $addresses = explode(',', $addressList);
        $fakedAddresses = [];
        foreach ($addresses as $address) {
            $fakedAddresses[] = new FakeAddress($address);
        }

        return $fakedAddresses;
    }

    if (!class_exists(FakeAddress::class)) {
        class FakeAddress
        {
            public $host = 'example.com';
            public $mailbox = 'joe';
            public $personal = 'joe example';

            /**
             * FakeAddress constructor.
             *
             * @param string $addressString
             */
            public function __construct($addressString)
            {
                $addressParts = explode('@', $addressString);
                $this->mailbox = trim($addressParts[0]);
                $this->host = trim($addressParts[1]);
                $this->personal = explode('.', $addressParts[1])[0];
            }
        }
    }
}
