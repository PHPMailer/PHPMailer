<?php
/*************************************************************************
 *                                                                       *
 * Converts HTML to formatted plain text                                 *
 *                                                                       *
 * Portions Copyright (c) 2005-2007 Jon Abernathy <jon@chuggnutt.com>    *
 *                                                                       *
 * This script is free software; you can redistribute it and/or modify   *
 * it under the terms of the GNU General Public License as published by  *
 * the Free Software Foundation; either version 2 of the License, or     *
 * (at your option) any later version.                                   *
 *                                                                       *
 * The GNU General Public License can be found at                        *
 * http://www.gnu.org/copyleft/gpl.html.                                 *
 *                                                                       *
 * This script is distributed in the hope that it will be useful,        *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of        *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the          *
 * GNU General Public License for more details.                          *
 *                                                                       *
 *************************************************************************/


class html2text
{

    /**
     *  Contains the HTML content to convert.
     *
     *  @var string $html
     *  @access public
     */
    public $html;

    /**
     *  Contains the converted, formatted text.
     *
     *  @var string $text
     *  @access public
     */
    public $text;

    /**
     *  Maximum width of the formatted text, in columns.
     *
     *  Set this value to 0 (or less) to ignore word wrapping
     *  and not constrain text to a fixed-width column.
     *
     *  @var integer $width
     *  @access public
     */
    public $width = 70;

    /**
     *  List of preg* regular expression patterns to search for,
     *  used in conjunction with $replace.
     *
     *  @var array $search
     *  @access public
     *  @see $replace
     */
    public $search = array(
        "/\r/",                                  // Non-legal carriage return
        "/[\n\t]+/",                             // Newlines and tabs
        '/<head[^>]*>.*?<\/head>/i',             // <head>
        '/<script[^>]*>.*?<\/script>/i',         // <script>s -- which strip_tags supposedly has problems with
        '/<style[^>]*>.*?<\/style>/i',           // <style>s -- which strip_tags supposedly has problems with
        '/<p[^>]*>/i',                           // <P>
        '/<br[^>]*>/i',                          // <br>
        '/<i[^>]*>(.*?)<\/i>/i',                 // <i>
        '/<em[^>]*>(.*?)<\/em>/i',               // <em>
        '/(<ul[^>]*>|<\/ul>)/i',                 // <ul> and </ul>
        '/(<ol[^>]*>|<\/ol>)/i',                 // <ol> and </ol>
        '/<li[^>]*>(.*?)<\/li>/i',               // <li> and </li>
        '/<li[^>]*>/i',                          // <li>
        '/<hr[^>]*>/i',                          // <hr>
        '/<div[^>]*>/i',                         // <div>
        '/(<table[^>]*>|<\/table>)/i',           // <table> and </table>
        '/(<tr[^>]*>|<\/tr>)/i',                 // <tr> and </tr>
        '/<td[^>]*>(.*?)<\/td>/i',               // <td> and </td>
        '/<span class="_html2text_ignore">.+?<\/span>/i'  // <span class="_html2text_ignore">...</span>
    );

    /**
     *  List of pattern replacements corresponding to patterns searched.
     *
     *  @var array $replace
     *  @access public
     *  @see $search
     */
    public $replace = array(
        '',                                     // Non-legal carriage return
        ' ',                                    // Newlines and tabs
        '',                                     // <head>
        '',                                     // <script>s -- which strip_tags supposedly has problems with
        '',                                     // <style>s -- which strip_tags supposedly has problems with
        "\n\n",                                 // <P>
        "\n",                                   // <br>
        '_\\1_',                                // <i>
        '_\\1_',                                // <em>
        "\n\n",                                 // <ul> and </ul>
        "\n\n",                                 // <ol> and </ol>
        "\t* \\1\n",                            // <li> and </li>
        "\n\t* ",                               // <li>
        "\n-------------------------\n",        // <hr>
        "<div>\n",                              // <div>
        "\n\n",                                 // <table> and </table>
        "\n",                                   // <tr> and </tr>
        "\t\t\\1\n",                            // <td> and </td>
        ""                                      // <span class="_html2text_ignore">...</span>
    );

    /**
     *  List of preg* regular expression patterns to search for,
     *  used in conjunction with $ent_replace.
     *
     *  @var array $ent_search
     *  @access public
     *  @see $ent_replace
     */
    public $ent_search = array(
        '/&(nbsp|#160);/i',                      // Non-breaking space
        '/&(quot|rdquo|ldquo|#8220|#8221|#147|#148);/i',
        // Double quotes
        '/&(apos|rsquo|lsquo|#8216|#8217);/i',   // Single quotes
        '/&gt;/i',                               // Greater-than
        '/&lt;/i',                               // Less-than
        '/&(copy|#169);/i',                      // Copyright
        '/&(trade|#8482|#153);/i',               // Trademark
        '/&(reg|#174);/i',                       // Registered
        '/&(mdash|#151|#8212);/i',               // mdash
        '/&(ndash|minus|#8211|#8722);/i',        // ndash
        '/&(bull|#149|#8226);/i',                // Bullet
        '/&(pound|#163);/i',                     // Pound sign
        '/&(euro|#8364);/i',                     // Euro sign
        '/&(amp|#38);/i',                        // Ampersand: see _converter()
        '/[ ]{2,}/',                             // Runs of spaces, post-handling
    );

    /**
     *  List of pattern replacements corresponding to patterns searched.
     *
     *  @var array $ent_replace
     *  @access public
     *  @see $ent_search
     */
    public $ent_replace = array(
        ' ',                                    // Non-breaking space
        '"',                                    // Double quotes
        "'",                                    // Single quotes
        '>',
        '<',
        '(c)',
        '(tm)',
        '(R)',
        '--',
        '-',
        '*',
        'Â£',
        'EUR',                                  // Euro sign. € ?
        '|+|amp|+|',                            // Ampersand: see _converter()
        ' ',                                    // Runs of spaces, post-handling
    );

    /**
     *  List of preg* regular expression patterns to search for
     *  and replace using callback function.
     *
     *  @var array $callback_search
     *  @access public
     */
    public $callback_search = array(
        '/<(a) [^>]*href=("|\')([^"\']+)\2([^>]*)>(.*?)<\/a>/i', // <a href="">
        '/<(h)[123456]( [^>]*)?>(.*?)<\/h[123456]>/i',         // h1 - h6
        '/<(b)( [^>]*)?>(.*?)<\/b>/i',                         // <b>
        '/<(strong)( [^>]*)?>(.*?)<\/strong>/i',               // <strong>
        '/<(th)( [^>]*)?>(.*?)<\/th>/i',                       // <th> and </th>
    );

    /**
     *  List of preg* regular expression patterns to search for in PRE body,
     *  used in conjunction with $pre_replace.
     *
     *  @var array $pre_search
     *  @access public
     *  @see $pre_replace
     */
    public $pre_search = array(
        "/\n/",
        "/\t/",
        '/ /',
        '/<pre[^>]*>/',
        '/<\/pre>/'
    );

    /**
     *  List of pattern replacements corresponding to patterns searched for PRE body.
     *
     *  @var array $pre_replace
     *  @access public
     *  @see $pre_search
     */
    public $pre_replace = array(
        '<br>',
        '&nbsp;&nbsp;&nbsp;&nbsp;',
        '&nbsp;',
        '',
        ''
    );

    /**
     *  Contains a list of HTML tags to allow in the resulting text.
     *
     *  @var string $allowed_tags
     *  @access public
     *  @see set_allowed_tags()
     */
    public $allowed_tags = '';

    /**
     *  Contains the base URL that relative links should resolve to.
     *
     *  @var string $url
     *  @access public
     */
    public $url;

    /**
     *  Indicates whether content in the $html variable has been converted yet.
     *
     *  @var boolean $_converted
     *  @access private
     *  @see $html, $text
     */
    private $_converted = false;

    /**
     *  Contains URL addresses from links to be rendered in plain text.
     *
     *  @var array $_link_list
     *  @access private
     *  @see _build_link_list()
     */
    private $_link_list = array();


    /**
     *  Various configuration options (able to be set in the constructor)
     *
     *  @var array $_options
     *  @access private
     */
    private $_options = array(

        // 'none'
        // 'inline' (show links inline)
        // 'nextline' (show links on the next line)
        // 'table' (if a table of link URLs should be listed after the text.
        'do_links' => 'inline',

        //  Maximum width of the formatted text, in columns.
        //  Set this value to 0 (or less) to ignore word wrapping
        //  and not constrain text to a fixed-width column.
        'width' => 70,
    );


    /**
     *  Constructor.
     *
     *  If the HTML source string (or file) is supplied, the class
     *  will instantiate with that source propagated, all that has
     *  to be done it to call get_text().
     *
     *  @param string $source HTML content
     *  @param boolean $from_file Indicates $source is a file to pull content from
     *  @param array $options Set configuration options
     *  @access public
     *  @return void
     */
    public function __construct( $source = '', $from_file = false, $options = array() )
    {
        $this->_options = array_merge($this->_options, $options);

        if ( !empty($source) ) {
            $this->set_html($source, $from_file);
        }

        $this->set_base_url();
    }

    /**
     *  Loads source HTML into memory, either from $source string or a file.
     *
     *  @param string $source HTML content
     *  @param boolean $from_file Indicates $source is a file to pull content from
     *  @access public
     *  @return void
     */
    public function set_html( $source, $from_file = false )
    {
        if ( $from_file && file_exists($source) ) {
            $this->html = file_get_contents($source);
        }
        else
            $this->html = $source;

        $this->_converted = false;
    }

    /**
     *  Returns the text, converted from HTML.
     *
     *  @access public
     *  @return string
     */
    public function get_text()
    {
        if ( !$this->_converted ) {
            $this->_convert();
        }

        return $this->text;
    }

    /**
     *  Prints the text, converted from HTML.
     *
     *  @access public
     *  @return void
     */
    public function print_text()
    {
        print $this->get_text();
    }

    /**
     *  Alias to print_text(), operates identically.
     *
     *  @access public
     *  @return void
     *  @see print_text()
     */
    public function p()
    {
        print $this->get_text();
    }

    /**
     *  Sets the allowed HTML tags to pass through to the resulting text.
     *
     *  Tags should be in the form "<p>", with no corresponding closing tag.
     *
     *  @access public
     *  @return void
     */
    public function set_allowed_tags( $allowed_tags = '' )
    {
        if ( !empty($allowed_tags) ) {
            $this->allowed_tags = $allowed_tags;
        }
    }

    /**
     *  Sets a base URL to handle relative links.
     *
     *  @access public
     *  @return void
     */
    public function set_base_url( $url = '' )
    {
        if ( empty($url) ) {
            if ( !empty($_SERVER['HTTP_HOST']) ) {
                $this->url = 'http://' . $_SERVER['HTTP_HOST'];
            } else {
                $this->url = '';
            }
        } else {
            // Strip any trailing slashes for consistency (relative
            // URLs may already start with a slash like "/file.html")
            if ( substr($url, -1) == '/' ) {
                $url = substr($url, 0, -1);
            }
            $this->url = $url;
        }
    }

    /**
     *  Workhorse function that does actual conversion (calls _converter() method).
     *
     *  @access private
     *  @return void
     */
    private function _convert()
    {
        // Variables used for building the link list
        $this->_link_list = array();

        $text = trim(stripslashes($this->html));

        // Convert HTML to TXT
        $this->_converter($text);

        // Add link list
        if (!empty($this->_link_list)) {
            $text .= "\n\nLinks:\n------\n";
            foreach ($this->_link_list as $idx => $url) {
                $text .= '[' . ($idx+1) . '] ' . $url . "\n";
            }
        }

        $this->text = $text;

        $this->_converted = true;
    }

    /**
     *  Workhorse function that does actual conversion.
     *
     *  First performs custom tag replacement specified by $search and
     *  $replace arrays. Then strips any remaining HTML tags, reduces whitespace
     *  and newlines to a readable format, and word wraps the text to
     *  $this->_options['width'] characters.
     *
     *  @param string Reference to HTML content string
     *
     *  @access private
     *  @return void
     */
    private function _converter(&$text)
    {
        // Convert <BLOCKQUOTE> (before PRE!)
        $this->_convert_blockquotes($text);

        // Convert <PRE>
        $this->_convert_pre($text);

        // Run our defined tags search-and-replace
        $text = preg_replace($this->search, $this->replace, $text);

        // Run our defined tags search-and-replace with callback
        $text = preg_replace_callback($this->callback_search, array($this, '_preg_callback'), $text);

        // Strip any other HTML tags
        $text = strip_tags($text, $this->allowed_tags);

        // Run our defined entities/characters search-and-replace
        $text = preg_replace($this->ent_search, $this->ent_replace, $text);

        // Replace known html entities
        $text = html_entity_decode($text, ENT_QUOTES);

        // Remove unknown/unhandled entities (this cannot be done in search-and-replace block)
        $text = preg_replace('/&([a-zA-Z0-9]{2,6}|#[0-9]{2,4});/', '', $text);

        // Convert "|+|amp|+|" into "&", need to be done after handling of unknown entities
        // This properly handles situation of "&amp;quot;" in input string
        $text = str_replace('|+|amp|+|', '&', $text);

        // Bring down number of empty lines to 2 max
        $text = preg_replace("/\n\s+\n/", "\n\n", $text);
        $text = preg_replace("/[\n]{3,}/", "\n\n", $text);

        // remove leading empty lines (can be produced by eg. P tag on the beginning)
        $text = ltrim($text, "\n");

        // Wrap the text to a readable format
        // for PHP versions >= 4.0.2. Default width is 75
        // If width is 0 or less, don't wrap the text.
        if ( $this->_options['width'] > 0 ) {
            $text = wordwrap($text, $this->_options['width']);
        }
    }

    /**
     *  Helper function called by preg_replace() on link replacement.
     *
     *  Maintains an internal list of links to be displayed at the end of the
     *  text, with numeric indices to the original point in the text they
     *  appeared. Also makes an effort at identifying and handling absolute
     *  and relative links.
     *
     *  @param string $link URL of the link
     *  @param string $display Part of the text to associate number with
     *  @access private
     *  @return string
     */
    private function _build_link_list( $link, $display, $link_override = null)
    {
        $link_method = ($link_override) ? $link_override : $this->_options['do_links'];
        if ($link_method == 'none')
            return $display;


        // Ignored link types
        if (preg_match('!^(javascript:|mailto:|#)!i', $link)) {
            return $display;
        }
        if (preg_match('!^([a-z][a-z0-9.+-]+:)!i', $link)) {
            $url = $link;
        }
        else {
            $url = $this->url;
            if (substr($link, 0, 1) != '/') {
                $url .= '/';
            }
            $url .= "$link";
        }

        if ($link_method == 'table')
        {
            if (($index = array_search($url, $this->_link_list)) === false) {
                $index = count($this->_link_list);
                $this->_link_list[] = $url;
            }

            return $display . ' [' . ($index+1) . ']';
        }
        elseif ($link_method == 'nextline')
        {
            return $display . "\n[" . $url . ']';
        }
        else // link_method defaults to inline
        {
            return $display . ' [' . $url . ']';
        }
    }

    /**
     *  Helper function for PRE body conversion.
     *
     *  @param string HTML content
     *  @access private
     */
    private function _convert_pre(&$text)
    {
        // get the content of PRE element
        while (preg_match('/<pre[^>]*>(.*)<\/pre>/ismU', $text, $matches)) {
            $this->pre_content = $matches[1];

            // Run our defined tags search-and-replace with callback
            $this->pre_content = preg_replace_callback($this->callback_search,
                array($this, '_preg_callback'), $this->pre_content);

            // convert the content
            $this->pre_content = sprintf('<div><br>%s<br></div>',
                preg_replace($this->pre_search, $this->pre_replace, $this->pre_content));
            // replace the content (use callback because content can contain $0 variable)
            $text = preg_replace_callback('/<pre[^>]*>.*<\/pre>/ismU',
                array($this, '_preg_pre_callback'), $text, 1);

            // free memory
            $this->pre_content = '';
        }
    }

    /**
     *  Helper function for BLOCKQUOTE body conversion.
     *
     *  @param string HTML content
     *  @access private
     */
    private function _convert_blockquotes(&$text)
    {
        if (preg_match_all('/<\/*blockquote[^>]*>/i', $text, $matches, PREG_OFFSET_CAPTURE)) {
            $level = 0;
            $diff = 0;
            $start = 0;
            $taglen = 0;
            foreach ($matches[0] as $m) {
                if ($m[0][0] == '<' && $m[0][1] == '/') {
                    $level--;
                    if ($level < 0) {
                        $level = 0; // malformed HTML: go to next blockquote
                    }
                    else if ($level > 0) {
                        // skip inner blockquote
                    }
                    else {
                        $end  = $m[1];
                        $len  = $end - $taglen - $start;
                        // Get blockquote content
                        $body = substr($text, $start + $taglen - $diff, $len);

                        // Set text width
                        $p_width = $this->_options['width'];
                        if ($this->_options['width'] > 0) $this->_options['width'] -= 2;
                        // Convert blockquote content
                        $body = trim($body);
                        $this->_converter($body);
                        // Add citation markers and create PRE block
                        $body = preg_replace('/((^|\n)>*)/', '\\1> ', trim($body));
                        $body = '<pre>' . htmlspecialchars($body) . '</pre>';
                        // Re-set text width
                        $this->_options['width'] = $p_width;
                        // Replace content
                        $text = substr($text, 0, $start - $diff)
                            . $body . substr($text, $end + strlen($m[0]) - $diff);

                        $diff = $len + $taglen + strlen($m[0]) - strlen($body);
                        unset($body);
                    }
                }
                else {
                    if ($level == 0) {
                        $start = $m[1];
                        $taglen = strlen($m[0]);
                    }
                    $level ++;
                }
            }
        }
    }

    /**
     *  Callback function for preg_replace_callback use.
     *
     *  @param  array PREG matches
     *  @return string
     */
    private function _preg_callback($matches)
    {
        switch (strtolower($matches[1])) {
            case 'b':
            case 'strong':
                return $this->_toupper($matches[3]);
            case 'th':
                return $this->_toupper("\t\t". $matches[3] ."\n");
            case 'h':
                return $this->_toupper("\n\n". $matches[3] ."\n\n");
            case 'a':
                // override the link method
                $link_override = null;
                if (preg_match("/_html2text_link_(\w+)/", $matches[4], $link_override_match))
                {
                    $link_override = $link_override_match[1];
                }
                // Remove spaces in URL (#1487805)
                $url = str_replace(' ', '', $matches[3]);
                return $this->_build_link_list($url, $matches[5], $link_override);
        }
    }

    /**
     *  Callback function for preg_replace_callback use in PRE content handler.
     *
     *  @param  array PREG matches
     *  @return string
     */
    private function _preg_pre_callback($matches)
    {
        return $this->pre_content;
    }

    /**
     * Strtoupper function with HTML tags and entities handling.
     *
     * @param string $str Text to convert
     * @return string Converted text
     */
    private function _toupper($str)
    {
        // string can containg HTML tags
        $chunks = preg_split('/(<[^>]*>)/', $str, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        // convert toupper only the text between HTML tags
        foreach ($chunks as $idx => $chunk) {
            if ($chunk[0] != '<') {
                $chunks[$idx] = $this->_strtoupper($chunk);
            }
        }

        return implode($chunks);
    }

    /**
     * Strtoupper multibyte wrapper function with HTML entities handling.
     *
     * @param string $str Text to convert
     * @return string Converted text
     */
    private function _strtoupper($str)
    {
        $str = html_entity_decode($str, ENT_COMPAT);

        if (function_exists('mb_strtoupper'))
            $str = mb_strtoupper($str);
        else
            $str = strtoupper($str);

        $str = htmlspecialchars($str, ENT_COMPAT);

        return $str;
    }
}