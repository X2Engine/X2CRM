<?php

/*************************************************************************************
* ===================================================================================*
* Software by: Danyuki Software Limited                                              *
* This file is part of Plancake.                                                     *
*                                                                                    *
* Copyright 2009-2010-2011 by:     Danyuki Software Limited                          *
* Support, News, Updates at:  http://www.plancake.com                                *
* Licensed under the LGPL version 3 license.                                         *                                                       *
* Danyuki Software Limited is registered in England and Wales (Company No. 07554549) *
**************************************************************************************
* Plancake is distributed in the hope that it will be useful,                        *
* but WITHOUT ANY WARRANTY; without even the implied warranty of                     *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                      *
* GNU Lesser General Public License v3.0 for more details.                           *
*                                                                                    *
* You should have received a copy of the GNU Lesser General Public License           *
* along with this program.  If not, see <http://www.gnu.org/licenses/>.              *
*                                                                                    *
**************************************************************************************
*
* Valuable contributions by:
* - Chris 
*
* **************************************************************************************/

/**
 * Extracts the headers and the body of an email
 * Obviously it can't extract the bcc header because it doesn't appear in the content
 * of the email.
 *
 * N.B.: if you deal with non-English languages, we recommend you install the IMAP PHP extension:
 * the Plancake PHP Email Parser will detect it and used it automatically for better results.
 * 
 * For more info, check:
 * https://github.com/plancake/official-library-php-email-parser
 * 
 * @author dan
 */
class PlancakeEmailParser {

    const PLAINTEXT = 1;
    const HTML = 2;

    /**
     *
     * @var boolean
     */    
    private $isImapExtensionAvailable = false;
    
    /**
     *
     * @var string
     */
    private $emailRawContent;

    /**
     *
     * @var associative array
     */
    protected $rawFields;

    /**
     *
     * @var array of string (each element is a line)
     */
    protected $rawBodyLines;

    /**
     *
     * @param string $emailRawContent
     */
    public function  __construct($emailRawContent) {
        $this->emailRawContent = $emailRawContent;

        $this->extractHeadersAndRawBody();
        
        if (function_exists('imap_open')) {
            $this->isImapExtensionAvailable = true;
        }
    }

    private function extractHeadersAndRawBody()
    {
        $lines = preg_split("/(\r?\n|\r)/", $this->emailRawContent);

        $currentHeader = '';

        $i = 0;
        foreach ($lines as $line)
        {
            if(self::isNewLine($line))
            {
                // end of headers
                $this->rawBodyLines = array_slice($lines, $i);
                break;
            }
            
            if ($this->isLineStartingWithPrintableChar($line)) // start of new header
            {
                preg_match('/([^:]+): ?(.*)$/', $line, $matches);
                $newHeader = strtolower($matches[1]);
                $value = $matches[2];
                $this->rawFields[$newHeader] = $value;
                $currentHeader = $newHeader;
            }
            else // more lines related to the current header
            {
                if ($currentHeader) { // to prevent notice from empty lines
                    $this->rawFields[$currentHeader] .= substr($line, 1);
                }
            }
            $i++;
        }
    }

    /**
     *
     * @return string (in UTF-8 format)
     * @throws Exception if a subject header is not found
     */
    public function getSubject()
    {
        if (!isset($this->rawFields['subject']))
        {
            throw new Exception("Couldn't find the subject of the email");
        }
        
        $ret = '';
        
        if ($this->isImapExtensionAvailable) {
            foreach (imap_mime_header_decode($this->rawFields['subject']) as $h) { // subject can span into several lines
                $charset = ($h->charset == 'default') ? 'US-ASCII' : $h->charset;
                $ret .=  iconv($charset, "UTF-8//TRANSLIT", $h->text);
            }
        } else {
            $ret = utf8_encode(iconv_mime_decode($this->rawFields['subject']));
        }
        
        return $ret;
    }

    /**
     *
     * @return array
     */
    public function getCc()
    {
        if (!isset($this->rawFields['cc']))
        {
            return array();
        }

        return explode(',', $this->rawFields['cc']);
    }

    /**
     *
     * @return array
     * @throws Exception if a to header is not found or if there are no recipient
     */
    public function getTo()
    {
        if ( (!isset($this->rawFields['to'])) || (!count($this->rawFields['to'])))
        {
            throw new Exception("Couldn't find the recipients of the email");
        }
        return explode(',', $this->rawFields['to']);
    }

    /**
     * return string - UTF8 encoded
     * 
     * Example of an email body
     * 
        --0016e65b5ec22721580487cb20fd
        Content-Type: text/plain; charset=ISO-8859-1

        Hi all. I am new to Android development.
        Please help me.

        --
        My signature

        email: myemail@gmail.com
        web: http://www.example.com

        --0016e65b5ec22721580487cb20fd
        Content-Type: text/html; charset=ISO-8859-1
     */
    public function getBody($returnType=self::PLAINTEXT)
    {
        $body = '';
        $detectedContentType = false;
        $contentTransferEncoding = null;
        $charset = 'ASCII';
        $waitingForContentStart = true;

        if ($returnType == self::HTML)
            $contentTypeRegex = '/^Content-Type: ?text\/html/i';
        else
            $contentTypeRegex = '/^Content-Type: ?text\/plain/i';
        
        // there could be more than one boundary
        preg_match_all('!boundary=(.*)$!mi', $this->emailRawContent, $matches);
        $boundaries = $matches[1];
        // sometimes boundaries are delimited by quotes - we want to remove them
        foreach($boundaries as $i => $v) {
            $boundaries[$i] = str_replace(array("'", '"'), '', $v);
        }
        
        foreach ($this->rawBodyLines as $line) {
            if (!$detectedContentType) {
                
                if (preg_match($contentTypeRegex, $line, $matches)) {
                    $detectedContentType = true;
                }
                
                if(preg_match('/charset=(.*)/i', $line, $matches)) {
                    $charset = strtoupper(trim($matches[1], '"')); 
                }       
                
            } else if ($detectedContentType && $waitingForContentStart) {
                
                if(preg_match('/charset=(.*)/i', $line, $matches)) {
                    $charset = strtoupper(trim($matches[1], '"')); 
                }                 
                
                if ($contentTransferEncoding == null && preg_match('/^Content-Transfer-Encoding: ?(.*)/i', $line, $matches)) {
                    $contentTransferEncoding = $matches[1];
                }                
                
                if (self::isNewLine($line)) {
                    $waitingForContentStart = false;
                }
            } else {  // ($detectedContentType && !$waitingForContentStart)
                // collecting the actual content until we find the delimiter
                
                // if the delimited is AAAAA, the line will be --AAAAA  - that's why we use substr
                if (is_array($boundaries)) {
                    if (in_array(substr($line, 2), $boundaries)) {  // found the delimiter
                        break;
                    }
                }
                $body .= $line . "\n";
            }
        }

        if (!$detectedContentType)
        {
            // if here, we missed the text/plain content-type (probably it was
            // in the header), thus we assume the whole body is what we are after
            $body = implode("\n", $this->rawBodyLines);
        }

        // removing trailing new lines
        $body = preg_replace('/((\r?\n)*)$/', '', $body);

        if ($contentTransferEncoding == 'base64')
            $body = base64_decode($body);
        else if ($contentTransferEncoding == 'quoted-printable')
            $body = quoted_printable_decode($body);        
        
        if($charset != 'UTF-8') {
            // FORMAT=FLOWED, despite being popular in emails, it is not
            // supported by iconv
            $charset = str_replace("FORMAT=FLOWED", "", $charset);
            
            $body = iconv($charset, 'UTF-8//TRANSLIT', $body);
            
            if ($body === FALSE) { // iconv returns FALSE on failure
                $body = utf8_encode($body);
            }
        }

        return $body;
    }

    /**
     * @return string - UTF8 encoded
     * 
     */
    public function getPlainBody()
    {
        return $this->getBody(self::PLAINTEXT);
    }

    /**
     * return string - UTF8 encoded
     */
    public function getHTMLBody()
    {
        return $this->getBody(self::HTML);
    }

    /**
     * N.B.: if the header doesn't exist an empty string is returned
     *
     * @param string $headerName - the header we want to retrieve
     * @return string - the value of the header
     */
    public function getHeader($headerName)
    {
        $headerName = strtolower($headerName);

        if (isset($this->rawFields[$headerName]))
        {
            return $this->rawFields[$headerName];
        }
        return '';
    }

    /**
     *
     * @param string $line
     * @return boolean
     */
    public static function isNewLine($line)
    {
        $line = str_replace("\r", '', $line);
        $line = str_replace("\n", '', $line);

        return (strlen($line) === 0);
    }

    /**
     *
     * @param string $line
     * @return boolean
     */
    private function isLineStartingWithPrintableChar($line)
    {
        return preg_match('/^[A-Za-z]/', $line);
    }
}
?>
