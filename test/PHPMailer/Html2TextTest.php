<?php

/**
 * PHPMailer - PHP email transport unit tests.
 * PHP version 5.5.
 *
 * @author    Marcus Bointon <phpmailer@synchromedia.co.uk>
 * @author    Andy Prevost
 * @copyright 2012 - 2020 Marcus Bointon
 * @copyright 2004 - 2009 Andy Prevost
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace PHPMailer\Test\PHPMailer;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\Test\TestCase;

/**
 * Test HTML to text conversion functionality.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::html2text
 */
final class Html2TextTest extends TestCase
{

    /**
     * Test converting an arbitrary HTML string into plain text.
     *
     * @dataProvider dataHtml2Text
     *
     * @param string $input    Arbitrary string, potentially containing HTML.
     * @param string $expected The expected function return value.
     * @param string $charset  Optional. Charset to use.
     */
    public function testHtml2Text($input, $expected, $charset = null)
    {
        if (isset($charset)) {
            $this->Mail->CharSet = $charset;
        }

        $result = $this->Mail->html2text($input);
        self::assertSame($expected, $result);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataHtml2Text()
    {
        return [
            'Plain text, no encoded entities, surrounded by whitespace' => [
                'input'    => '  Lorem ipsum dolor sit amet, consectetur adipiscing elit.  ',
                'expected' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            ],
            'Plain text with encoded entities' => [
                'input'    => 'I&ntilde;t&euml;rn&acirc;ti&ocirc;n&agrave;liz&aelig;ti&oslash;n',
                'expected' => 'Iñtërnâtiônàlizætiøn',
                'charset'  => PHPMailer::CHARSET_UTF8
            ],

            'Simple HTML, including HTML comment and encoded quotes' => [
                'input'    => '<p>Test paragraph.</p>'
                    . '<!-- Comment -->'
                    . ' <a href="#fragment">Other text with &#39; and &quot;</a>',
                'expected' => 'Test paragraph. Other text with \' and "',
            ],
            'Simple HTML, including self-closing tags' => [
                'input'    => '<p>Test<br/>paragraph<br /><img src="file.png" alt="alt text" />.</p>',
                'expected' => 'Testparagraph.',
            ],
            'Simple HTML, paragraph fusing' => [
                'input'    => '<div>
<P style="color:blue;">color is blue</P><p>size is <span style="font-size:200%;">huge</span></p>
<p>material is wood</p>
</div>',
                'expected' => 'color is bluesize is huge
material is wood',
            ],
            'Full HTML message with head, title, body etc' => [
                'input'    => <<<EOT
<html>
    <head>
        <title>HTML email test</title>
    </head>
    <body>
        <h1>PHPMailer does HTML!</h1>
        <p>This is a <strong>test message</strong> written in HTML.<br>
        Go to <a href="http://code.google.com/a/apache-extras.org/p/phpmailer/">
        http://code.google.com/a/apache-extras.org/p/phpmailer/</a>
        for new versions of PHPMailer.</p>
        <p>Thank you!</p>
    </body>
</html>
EOT
                ,
                // Note: be careful when updating & saving this file. The the trailing space on
                // the line with "Go to " needs to be preserved!
                'expected' => <<<EOT
PHPMailer does HTML!
        This is a test message written in HTML.
        Go to 
        http://code.google.com/a/apache-extras.org/p/phpmailer/
        for new versions of PHPMailer.
        Thank you!
EOT
                ,
            ],
            // PHP bug: https://bugs.php.net/bug.php?id=78346
            'Plain text, with PHP short open echo tags' => [
                'input'    => '<?= \'<?= 1 ?>\' ?>2',
                'expected' => '2',
            ],
            'HTML with script tag' => [
                'input'    => 'lorem<script>alert("xss")</script>ipsum',
                'expected' => 'loremipsum',
            ],
            'HTML with style tag with content. uppercase HTML tags' => [
                'input'    => "lorem<STYLE>* { display: 'none' }</STYLE>ipsum",
                'expected' => 'loremipsum',
            ],
            'HTML with script tag nested in style tag' => [
                'input'    => "lorem<style>* { display: 'none' }<script>alert('xss')</script></style>ipsum",
                'expected' => 'loremipsum',
            ],

            'Plain text which will turn into HTML script tag on decoding' => [
                'input'    => 'lorem&lt;script&gt;alert("xss")&lt;/script&gt;ipsum',
                'expected' => 'lorem<script>alert("xss")</script>ipsum',
            ],
            'HTML with a "less than" sign in the text' => [
                'input'    => '<p><span style="color: #ff0000; background-color: #000000;">Complex</span> '
                    . '<span style="font-family: impact,chicago;">text <50% </span> '
                    . '<a href="http://exempledomain.com/"><em>with</em></a> '
                    . '<span style="font-size: 36pt;"><strong>tags</strong></span></p>',
                'expected' => 'Complex text',
            ],
            'HTML with an encoded "less than" sign in the text' => [
                'input'    => '<p><span style="color: #ff0000; background-color: #000000;">Complex</span> '
                    . '<span style="font-family: impact,chicago;">text &lt;50% </span> '
                    . '<a href="http://exempledomain.com/"><em>with</em></a> '
                    . '<span style="font-size: 36pt;"><strong>tags</strong></span></p>',
                'expected' => 'Complex text <50%  with tags',
            ],
        ];
    }

    /**
     * Test the use of the `$callback` parameter to use a custom callback function or fall back to the
     * native implementation.
     *
     * @dataProvider dataHtml2TextAdvanced
     *
     * @param string   $input    Arbitrary string, potentially containing HTML.
     * @param callable $callback A callback function to use.
     * @param string   $expected The expected function return value.
     */
    public function testHtml2TextAdvanced($input, $callback, $expected)
    {
        $result = $this->Mail->html2text($input, $callback);
        self::assertSame($expected, $result);
    }

    /**
     * Data provider.
     *
     * Note: the callbacks used here are inherently unsafe, but that's not the point
     * of the test. The point is to make sure that the function logic correctly chooses
     * the use of the native conversion versus a provided callback function, not to test
     * that the callback does the conversion correctly.
     *
     * @return array
     */
    public function dataHtml2TextAdvanced()
    {
        return [
            'No HTML, simple (unsafe) function name callback' => [
                'input'    => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'callback' => 'strip_tags',
                'expected' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            ],
            'Has HTML, simple (unsafe) closure callback' => [
                'input'    => 'Lorem <div>ipsum</div> dolor sit amet<br/>,'
                    . ' consectetur <script>adipiscing()</script> elit.',
                'callback' => static function ($html) {
                    return strip_tags($html);
                },
                'expected' => 'Lorem ipsum dolor sit amet, consectetur adipiscing() elit.',
            ],
            'Has HTML, simple (unsafe) static class method callback' => [
                'input'    => 'Lorem <div>ipsum</div> dolor sit amet<br/>,'
                    . ' consectetur <script>adipiscing()</script> elit.',
                'callback' => [__CLASS__, 'methodBasedCallback'],
                'expected' => 'Lorem ipsum dolor sit amet, consectetur adipiscing() elit.',
            ],
            'Has HTML, simple (unsafe) object method callback' => [
                'input'    => 'Lorem <div>ipsum</div> dolor sit amet<br/>,'
                    . ' consectetur <script>adipiscing()</script> elit.',
                'callback' => [$this, 'methodBasedCallback'],
                'expected' => 'Lorem ipsum dolor sit amet, consectetur adipiscing() elit.',
            ],
            'Has HTML, explicitly use internal converter (callback = true)' => [
                'input'    => 'Lorem <div>ipsum</div> dolor sit amet<br/>,'
                    . ' consectetur <script>adipiscing()</script> elit.',
                'callback' => true,
                'expected' => 'Lorem ipsum dolor sit amet, consectetur  elit.',
            ],
            'Has HTML, use internal converter due to passing invalid callback (function)' => [
                'input'    => 'Lorem <div>ipsum</div> dolor sit amet<br/>,'
                    . ' consectetur <script>adipiscing()</script> elit.',
                'callback' => 'functionwhichdoesnotexist',
                'expected' => 'Lorem ipsum dolor sit amet, consectetur  elit.',
            ],
            'Has HTML, use internal converter due to passing invalid callback (static method)' => [
                'input'    => 'Lorem <div>ipsum</div> dolor sit amet<br/>,'
                    . ' consectetur <script>adipiscing()</script> elit.',
                'callback' => ['claswhichdoesnotexist', 'foo'],
                'expected' => 'Lorem ipsum dolor sit amet, consectetur  elit.',
            ],
        ];
    }

    /**
     * Simplistic callback function.
     */
    public static function methodBasedCallback($html)
    {
        return strip_tags($html);
    }
}
