<?php

/**
 * SMTP low memory example.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * This class demonstrates sending an already-built RFC822 message via SMTP
 * by extending PHPMailer's SMTP class.
 * It uses less memory than PHPMailer's usual approach because it keeps
 * the message as a single string rather than splitting its lines into
 * an array, which can consume very large amounts of memory if you have
 * large attachments. The downside is that it's somewhat slower.
 * This is mainly of academic interest, but shows how you can change how
 * core classes work without having to alter the library itself.
 */
class SMTPLowMemory extends SMTP
{
    public function data($msg_data)
    {
        //This will use the standard timelimit
        if (!$this->sendCommand('DATA', 'DATA', 354)) {
            return false;
        }

        /* The server is ready to accept data!
         * According to rfc821 we should not send more than 1000 characters on a single line (including the LE)
         * so we will break the data up into lines by \r and/or \n then if needed we will break each of those into
         * smaller lines to fit within the limit.
         * We will also look for lines that start with a '.' and prepend an additional '.' (which does not count
         * towards the line-length limit), in order to implement the "dot stuffing" required by RFC5321 sections:
         * https://datatracker.ietf.org/doc/html/rfc5321#section-4.5.2
         * https://datatracker.ietf.org/doc/html/rfc5321#section-4.5.3.1.6.
         */

        //Normalize line breaks
        $msg_data = str_replace(["\r\n", "\r"], "\n", $msg_data);

        /* To distinguish between a complete RFC822 message and a plain message body, we check if the first field
         * of the first line (':' separated) does not contain a space then it _should_ be a header and we will
         * process all lines before a blank line as headers.
         */

        $firstline = substr($msg_data, 0, strcspn($msg_data, "\n", 0));
        $field = substr($firstline, 0, strpos($firstline, ':'));
        $in_headers = false;
        if (!empty($field) && strpos($field, ' ') === false) {
            $in_headers = true;
        }

        $offset = 0;
        $len = strlen($msg_data);
        while ($offset < $len) {
            //Get position of next line break
            $linelen = strcspn($msg_data, "\n", $offset);
            //Get the next line
            $line = substr($msg_data, $offset, $linelen);
            //Remember where we have got to
            $offset += ($linelen + 1);
            $lines_out = [];
            if ($in_headers && $line === '') {
                $in_headers = false;
            }
            //We need to break this line up into several smaller lines
            //This is a small micro-optimisation: isset($str[$len]) is equivalent to (strlen($str) > $len)
            while (isset($line[self::MAX_LINE_LENGTH])) {
                //Working backwards, try to find a space within the last MAX_LINE_LENGTH chars of the line to break on
                //so as to avoid breaking in the middle of a word
                $pos = strrpos(substr($line, 0, self::MAX_LINE_LENGTH), ' ');
                //Deliberately matches both false and 0
                if (!$pos) {
                    //No nice break found, add a hard break
                    $pos = self::MAX_LINE_LENGTH - 1;
                    $lines_out[] = substr($line, 0, $pos);
                    $line = substr($line, $pos);
                } else {
                    //Break at the found point
                    $lines_out[] = substr($line, 0, $pos);
                    //Move along by the amount we dealt with
                    $line = substr($line, $pos + 1);
                }
                //If processing headers add a LWSP-char to the front of new line RFC822 section 3.1.1
                if ($in_headers) {
                    $line = "\t" . $line;
                }
            }
            $lines_out[] = $line;

            //Send the lines to the server
            foreach ($lines_out as $line_out) {
                //RFC2821 section 4.5.2
                if (!empty($line_out) && $line_out[0] === '.') {
                    $line_out = '.' . $line_out;
                }
                $this->client_send($line_out . self::LE);
            }
        }

        //Message data has been sent, complete the command
        //Increase timelimit for end of DATA command
        $savetimelimit = $this->Timelimit;
        $this->Timelimit *= 2;
        $result = $this->sendCommand('DATA END', '.', 250);
        //Restore timelimit
        $this->Timelimit = $savetimelimit;

        return $result;
    }
}

//To make PHPMailer use our custom SMTP class, we need to give it an instance
$mail = new PHPMailer(true);
$mail->setSMTPInstance(new SMTPLowMemory());
//Now carry on as normal
