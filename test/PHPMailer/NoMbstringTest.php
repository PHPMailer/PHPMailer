<?php

namespace PHPMailer\PHPMailer;

use PHPMailer\PHPMailer\PHPMailer;
use PHPUnit\Framework\TestCase;

/**
 * Test fallback behavior when mbstring extension is not available
 */
class NoMbstringTest extends TestCase
{
    /**
     * @var PHPMailer
     */
    protected $mail;

    /**
     * Backup of mbstring functions
     */
    protected static $mbstringFunctions = [
        'mb_strlen' => null,
        'mb_substr' => null,
    ];

    /**
     * Set up before each test
     */
    protected function setUp(): void
    {
        $this->mail = new PHPMailer();
        $this->mail->CharSet = PHPMailer::CHARSET_UTF8;
        
        // Backup mbstring functions if they exist
        foreach (array_keys(self::$mbstringFunctions) as $function) {
            if (function_exists($function)) {
                self::$mbstringFunctions[$function] = $function;
                $this->disableFunction($function);
            }
        }
    }

    /**
     * Restore mbstring functions after each test
     */
    protected function tearDown(): void
    {
        // Restore mbstring functions
        foreach (self::$mbstringFunctions as $function => $original) {
            if ($original !== null) {
                $this->restoreFunction($function);
            }
        }
    }

    /**
     * Test that hasMultiBytes works without mbstring
     */
    public function testHasMultiBytesWithoutMbstring()
    {
        // Test with ASCII string (should return false)
        $this->assertFalse($this->mail->hasMultiBytes('ASCII string'));
        
        // Test with multibyte string (should return true)
        $this->assertTrue($this->mail->hasMultiBytes('Multibyte string: ñáéíóú'));
    }

    /**
     * Test that base64EncodeWrapMB works without mbstring
     */
    public function testBase64EncodeWrapMBWithoutMbstring()
    {
        $testString = 'This is a test string with multibyte characters: ñáéíóú';
        $encoded = $this->mail->base64EncodeWrapMB($testString);
        
        // The encoded string should not be empty
        $this->assertNotEmpty($encoded, 'Encoded string is empty');
        
        // When decoded, it should match the original string
        $normalized = preg_replace('/\s+/', '', $encoded);
        $decoded = base64_decode($normalized, true);
        $this->assertNotFalse($decoded, 'Failed to decode base64 string');
        $this->assertEquals($testString, $decoded, 'Decoded string does not match original');
    }

    /**
     * Test that encodeString works without mbstring
     */
    public function testEncodeStringWithoutMbstring()
    {
        $testString = 'This is a test string with multibyte characters: ñáéíóú';
        $encoded = $this->mail->encodeString($testString, PHPMailer::ENCODING_BASE64);
        
        // The encoded string should not be empty
        $this->assertNotEmpty($encoded, 'Encoded string is empty');
        
        // When decoded, it should match the original string
        $normalized = preg_replace('/\s+/', '', $encoded);
        $decoded = base64_decode($normalized, true);
        $this->assertNotFalse($decoded, 'Failed to decode base64 string');
        $this->assertEquals($testString, $decoded, 'Decoded string does not match original');
    }

    /**
     * Disable a function for testing purposes
     * 
     * @param string $functionName Name of the function to disable
     */
    private function disableFunction($functionName)
    {
        $namespace = __NAMESPACE__;
        $code = <<<EOT
namespace {$namespace};
if (!function_exists('{$functionName}')) {
    function {$functionName}() {
        throw new \RuntimeException('{$functionName} should not be called in this test');
    }
}
EOT;
        eval($code);
    }

    /**
     * Restore a function that was previously disabled
     * 
     * @param string $functionName Name of the function to restore
     */
    private function restoreFunction($functionName)
    {
        $namespace = __NAMESPACE__;
        $code = <<<EOT
namespace {$namespace};
if (function_exists('{$functionName}_backup')) {
    function {$functionName}() {
        return call_user_func_array('{$functionName}_backup', func_get_args());
    }
}
EOT;
        eval($code);
    }
}
