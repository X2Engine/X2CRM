<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




/**
 * Standalone environmentally-agnostic content/message feedback utility.
 *
 * In the scope of a web request, it will respond via JSON (i.e. for use in an
 * API or AJAX response action). When run in a command line interface, it will
 * echo messages without exiting.
 *
 * Setting elements of an object of this class (using the {@link ArrayAccess}
 * implementation) will control the properties of the JSON that is returned when
 * using it in a web request.
 *
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class ResponseUtil implements ArrayAccess {

    /**
     * Exit on non-fatal PHP errors.
     *
     * If set to true, the error handler {@link respondWithError} will force a
     * premature response for any PHP error, even if it's not of type E_ERROR.
     * 
     * @var bool 
     */
    public static $exitNonFatal = false;

    /**
     * The default HTTP status code to use when handling an internal error.
     *
     * This can be set to 200 when dealing with client-side code that cannot
     * retrieve response data if the response code is not 200. This would
     * thus allow user-friendly error reporting.
     *
     * @var integer
     */
    public static $errorCode = 500;

    /**
     * Whether to include or ignore any unintentional output
     *
     * If false, any extra output generated within the scope of the response (i.e.
     * error messages) will be excluded from the response altogether.
     * @var boolean
     */
    public static $includeExtraneousOutput = false;

   /**
     * Shutdown method.
     *
     * Can be set to, for instance, "Yii::app()->end();" for a graceful Yii
     * shutdown that saves/rotates logs and performs other useful/appropriate
     * operations before terminating the PHP thread.
     *
     * @var string|array|closure
     */
    public static $shutdown = 'die();';

    /**
     * Produce extended error traces in responses triggered by error handlers.
     * @var type
     */
    public static $longErrorTrace = false;

    /**
     * Override body.
     *
     * If left unset, the content type header will be set to JSON, and the
     * response body will be {@link _properties}, encoded in JSON. Otherwise,
     * any content type can be used, andthis property will be returned instead.
     * @var string
     */
    public $body;

    /**
     * HTTP header fields.
     *
     * The default of the "Content-type" field is JSON for ease of use, since
     * it's expected that this class will be used mostly to compose responses
     * in JSON format.
     * 
     * @var array
     */
    public $httpHeader = array(
        'Content-Type' => 'application/json'
    );

    /**
     * Specifies, if true, that a response is already in progress.
     *
     * This is used to avoid double-responding when using
     * {@link respondFatalErrorMessage} as a shutdown function for handling
     * fatal errors.
     * 
     * @var bool
     */
    private static $_responding = false;

    /**
     * Response singleton (there can only be one response at a time)
     * @var ResponseUtil
     */
    private static $_response = null;

    private static $_statusMessages = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type', // Incorrect content type in request
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity', // Validation errors
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        444 => 'No Response',
        494 => 'Request Header Too Large',
        495 => 'Cert Error',
        497 => 'HTTP to HTTPS',
        499 => 'Client Closed Request',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
        511 => 'Network Authentication Required'
    );
    
    /**
     * Properties of the response.
     *
     * In the case of responding to a web request, the response will be this
     * array encoded in JSON. When setting "indexes" of this class using the
     * array access method, this is the array where the values are stored.
     * @var array
     */
    private $_properties = array();
    
    /**
     * HTTP status code when applicable.
     *
     * The default is 200, meaning no error.
     *
     * @var type
     */
    private $_status = 200;

    /**
     * Performs the shutdown code.
     */
    public static function end() {
        switch(gettype(self::$shutdown)) {
            case 'string':
                // Interpret as a snippet of PHP code
                eval(self::$shutdown);
                break;
            case 'closure':
            case 'object':
                // Interpret as a function
                $shutdown = self::$shutdown;
                $shutdown();
                break;
            default:
                die();
        }
    }

    /**
     * Returns the current response singleton object.
     */
    public static function getObject() {
        if(self::$_response instanceof ResponseUtil) {
            return self::$_response;
        } else {
            return false;
        }
    }

    /**
     * Returns the static array of status messages, i.e. for reference
     * @return array
     */
    public static function getStatusMessages() {
        return self::$_statusMessages;
    }

    /**
     * Returns true or false based on whether or not the current thread of PHP
     * is being run from the command line.
     * @return bool
     */
    public static function isCli(){
        return (empty($_SERVER['SERVER_NAME']) || php_sapi_name()==='cli');
    }
    /**
     * Universal, web-agnostic response function.
     *
     * Responds with a JSON and closes the connection if used in a web request;
     * merely echoes the response message (but optionally exits) otherwise.
     *
     * @param type $message The message to respond with.
     * @param bool $error Indicates that an error has occurred
     * @param bool $fatal Shut down PHP thread after printing the message
     * @param string $shutdown Optional shutdown expression to be evaluated; it must halt the current PHP process.
     */
    public static function respond($message, $error = false, $fatal = false){
        if(self::isCli()){ // Command line interface message
            self::$_responding = true;
            echo trim($message)."\n";
            if($error && $fatal)
                self::end();
        } else { // One-off JSON response to HTTP client
            if(!isset(self::$_response)) {
                self::$_response = new ResponseUtil(array());
            }
            // Default error code if there's an error; default/previously-set
            // response code otherwise.
            self::$_response->sendHttp($error?self::$errorCode:null,$message,$error);
        }
    }

    /**
     * Error handler method that uses the web-agnostic response method.
     *
     * This is disabled by default; fatal errors should ordinarily be caught by
     * {@link respondFatalErrorMessage()}. This can be enabled for debugging
     * purposes via setting {@link exitNonFatal} to true.
     *
     * @param type $no
     * @param type $st
     * @param type $fi
     * @param type $ln
     */
    public static function respondWithError($no, $st, $fi = Null, $ln = Null){
        if(self::$exitNonFatal){
            $message = "Error [$no]: $st $fi L$ln";
            if(self::$longErrorTrace){
                ob_start();
                debug_print_backtrace();
                $message .= "; Trace:\n".ob_get_contents();
                ob_end_clean();
            }
            self::respond($message, true);
        }
    }

    /**
     * Shutdown function for handling fatal errors not caught by
     * {@link respondWithError()}.
     */
    public static function respondFatalErrorMessage(){
        $error = error_get_last();
        if($error != null && !self::$_responding){
            $errno = $error["type"];
            $errfile = $error["file"];
            $errline = $error["line"];
            $errstr = $error["message"];
            self::respond("PHP ".($errno == E_PARSE ? 'parse' : 'fatal')." error [$errno]: $errstr in $errfile L$errline",true);
        }
    }

    /**
     * @param Exception $e The uncaught exception
     */
    public static function respondWithException($e){
        $message = 'Exception: "'.$e->getMessage().'" in '.$e->getFile().' L'.$e->getLine()."\n";
        if(self::$longErrorTrace){
            $message .= "; Trace:\n";
            foreach($e->getTrace() as $stackLevel){
                if(!empty($stackLevel['file']) && !empty($stackLevel['line'])){
                    $message .= $stackLevel['file'].' L'.$stackLevel['line'].' ';
                }
                if(!empty($stackLevel['class'])){
                    $message .= $stackLevel['class'];
                    $message .= '->';
                }
                if(!empty($stackLevel['function'])){
                    $message .= $stackLevel['function'];
                    $message .= "();";
                }
                $message .= "\n";
            }
        }
        self::respond($message, true);
    }

    /**
     * Obtain an appropriate message for a given HTTP status code.
     *
     * @param integer $status
     * @return string
     */
    public static function statusMessage($code){
        $codes = self::$_statusMessages;
        return isset($codes[$code]) ? $codes[$code] : '';
    }

    //////////////////////
    // Instance Methods //
    //////////////////////

    /**
     * Constructor.
     * If one tries to instantiate two {@link ResponseUtil} objects, an
     * exception will be thrown. The idea is that there should only ever be one
     * response happening at a time.
     *
     * @param array $properties Initial response properties
     */
    public function __construct(){
        if(self::$_response instanceof ResponseUtil){
            throw new Exception('A response has already been declared.');
        }
        self::$_response = $this;
        if(!self::isCli()){
            // Collect any extraneous output so that it doesn't get sent before
            // the intended HTTP header gets sent:
            ob_start();
        }
    }


    /////////////////////////////
    // Array Interface Methods //
    /////////////////////////////

    /**
     * Array interface method from {@link ArrayAccess}
     * @param type $offset
     * @return type
     */
    public function offsetExists($offset){
        return array_key_exists($offset, $this->_properties);
    }

    /**
     * Array interface method from {@link ArrayAccess}
     * @param type $offset
     * @return type
     */
    public function offsetGet($offset){
        return $this->_properties[$offset];
    }

    /**
     * Array interface method from {@link ArrayAccess}
     * @param type $offset
     * @param type $value
     */
    public function offsetSet($offset, $value){
        $this->_properties[$offset] = $value;
    }

    /**
     * Array interface method from {@link ArrayAccess}
     * @param type $offset
     */
    public function offsetUnset($offset){
        unset($this->_properties[$offset]);
    }
    
    /**
     * Sends a HTTP response back to the client.
     *
     * @param integer $status The status code to use
     * @param type $message
     * @param type $error
     * @throws Exception
     */
    public function sendHttp($status=null, $message = '', $error = null){
        self::$_responding = true;
        // Close the output buffer; it's now safe to do so, since the header
        // will soon be sent.
        $output = ob_get_clean();
        ob_end_clean();
        $extraOutput = self::$includeExtraneousOutput && !empty($output);
        $status = $status === null ? ((bool)$error ? self::$errorCode : 200) : $status;

        // Set the response content
        if($status !== null && !array_key_exists((integer) $status,self::$_statusMessages)){
            // Invalid call to this method. Fail noisily.
            $this->_status = self::$errorCode;
            $body = '{"error":true,"message":"Internal server error: invalid or '
                    . 'non-numeric HTTP response status code specifed.","status":500}';
        } else if(!extension_loaded('json') || isset($this->body)) {
            // We might be doing something other than responding in JSON
            if(!isset($this->body)){
                if(strpos($this->httpHeader['Content-Type'],'application/json')===0){
                    // JSON-format responding in use but not available
                    $this->_status = self::$errorCode;
                    $body = '{"error":true,"message":"The JSON PHP extension is required,'
                            . ' but this server lacks it.","status":'.$this->_status.'}';
                } else {
                    // Simply echo the message if JSON isn't available.
                    $this->_status = $status;
                    $body = ($extraOutput?($output.' '):'').$message;
                }
            } else {
                // The "body" property is in use, which overrides the standard
                // way of responding with JSON-encoded properties
                $this->_status = $status;
                $body = ($extraOutput?($output.' '):'').$this->body;
            }
        } else {
            if($status != null) {
                // Override status. Loose comparison is in use because zero is
                // an invalid HTTP response code and expected only of certain
                // cURL libraries when the connection could not be established.
                $this->_status = $status;
            }
            $response = $this->_properties;
            
            // Set universal response properties:
            if(empty($message) && !empty($response['message']))
                $message = $response['message'];
            $response['message'] = $message.($extraOutput
                    ? " Note, extraneous output was generated in the scope of this response: $output"
                    : '');
            $response['error'] = $error === null
                    ? $this->_status >= 400
                    : (bool) $error;
            // Include the status code in the envelope for clients that can't
            // read HTTP headers:
            $response['status'] = $this->_status;
            // Compose the body of the response as a JSON-encoded object:
            $body = json_encode($response);
        }

        // Send the response
        $this->sendHttpHeader();
        echo $body;

        // Shut down
        self::$_response = null;
        self::end();
    }

    /**
     * Sends HTTP headers. This method should be called before any content is sent.
     * 
     * @param bool $replace The argument sent to header as the replacement flag.
     */
    protected function sendHttpHeader($replace = true){
        header(sprintf("HTTP/1.1 %d %s", $this->_status, self::statusMessage($this->_status)), $replace, $this->_status);
        foreach($this->httpHeader as $field => $value){
            header("$field: $value", $replace, $this->_status);
        }
    }

    /**
     * Sets {@link _properties}
     * @param array $properties
     */
    public function setProperties(array $properties) {
        $this->_properties = $properties;
    }
}
?>
