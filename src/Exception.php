<?php
/**
 * Created by PhpStorm.
 * User: marcus
 * Date: 09/11/2015
 * Time: 17:10
 */

namespace PHPMailer\PHPMailer;

/**
 * PHPMailer exception handler
 * @package PHPMailer
 */
class Exception extends \Exception
{
    /**
     * Prettify error message output
     * @return string
     */
    public function errorMessage()
    {
        $errorMsg = '<strong>' . $this->getMessage() . "</strong><br />\n";
        return $errorMsg;
    }
}
