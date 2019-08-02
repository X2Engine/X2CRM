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
 * (experimental) class for interfacing with the X2Engine REST API
 *
 * Currently supports querying, finding by attributes or primary key,
 * creating, updating and deleting records.
 *
 * @todo Needs test case
 * @todo Add methods for creating relationships, adding tags, etc.
 */
class Api2Model {

    const ERR_NOSESSION = 1;
    const ERR_CONNECTION = 2;
    const ERR_SERVER = 3;
 
    /**
     * Stores metadata associated with the application(s) working with.
     * 
     * Indexed by base URL.
     *
     * @var array
     */
    private static $_appMeta = array();

    /**
     * Flat list of names of columns in the data model.
     */
    private static $_attributeNames = array();

    /**
     * Stores model fields. 
     *
     * Indexed as:
     * 
     * [session name] => [model class] => [field name] => [permission]
     *
     * Where [permission] is 0 for no access, 1 for read-only access, 
     * or 2 for read-write access. See:
     *
     * http://wiki.x2engine.com/wiki/REST_API_Reference#Field-Level_Permissions
     * 
     * @var array 
     */
    private static $_fieldPermissions = array();

    /**
     * Stores fields metadata.
     * 
     * All metadata obtained via the zapierFields action is stored here.
     *
     * Indexed as:
     *
     * [session name] => [model class] => [field name] => [metadata] 
     * 
     * For futher information, see: 
     * 
     * http://wiki.x2engine.com/wiki/REST_API_Reference#Fields
     *
     * @var array
     */
    private static $_fields = array();

    /**
     * HTTP response codes.
     */
    private static $_statusCodes;

    /**
     * Defines status messages and response codes.
     * 
     * Taken from {@link ResponseUtil}
     * 
     * @var array
     */
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
     * Stores access credentials. 
     *
     * Each element is an array:
     * 0. Base URL; includes the full URL up to before "api2". For 
     *    example, if https://x2engine.example.com/index.php/api2
     *    is where requests are getting sent to, this should be
     *    https://x2engine.example.com/index.php
     * 1. User. The username in the X2Engine installation that resides
     *    at the base URL.
     * 2. API Key associated with the user.
     *    
     * @var array 
     */
    private static $_sessions = array();

    /**
     * Attributes of the current model
     */
    private $_attributes;

    /**
     * The model class of the current instance, i.e. Contacts.
     *
     * @var string
     */
    private $_class;

    /**
     * The name of the API "session" that the current model uses.
     */
    private $_session;

    /**
     * Initiates a "session"; stores a set of credentials.
     */
    public static function authenticate($baseUrl,$user,$key,
            $sessionName='default') {
        self::$_sessions[$sessionName] = array(
            $baseUrl,
            $user,
            $key
        );
        // Test the connection
        $ch = self::curlHandle('appInfo.json','GET',$sessionName);
        self::$_appMeta[$baseUrl] = self::send($ch);
    }

    /**
     * Constructs a cURL resource for making a HTTP request to the API.
     * 
     * @param string $uri The relative URI within the API without the
     *   leading slash, i.e. for index.php/api2/hooks, "hooks"
     * @param string $method The HTTP method, i.e. GET, POST, PUT, 
     *   DELETE. Not case-sensitive.
     * @param array|string $payload The body of the request to send.
     * @param string $sessionName The name of the set of credentials to
     *   use for the API transaction.
     */
    public static function curlHandle($uri,$method='GET',$sessionName='default') {
        // Get API connection details
        if(!isset(self::$_sessions[$sessionName])) {
            throw new Api2Exception("Cannot use API. Declare a connection first " .
                "using Api2Model::authenticate()",
                self::ERR_NOSESSION);
        }
        list($baseUrl,$user,$key) = self::$_sessions[$sessionName];

        // Normalize parameters:
        $uri = '/'.ltrim($uri,'/');
        $method = strtoupper($method);

        // Open a cURL connection:
        $ch = curl_init("$baseUrl/api2/$uri");
        if(!isset(self::$_statusCodes)) {
            self::$_statusCodes = array_keys(self::$_statusMessages);
        }
        $curlOpts = array(
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => "$user:$key", 
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json; charset=utf-8'
            ),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP200ALIASES => self::$_statusCodes
        );

        // Set options particular to the request type:
        if($method == 'POST') {
            $curlOpts[CURLOPT_POST] = true;
        } else if($method != 'GET') {
            $curlOpts[CURLOPT_CUSTOMREQUEST] = $method;
        } 

        // Configure and return:
        curl_setopt_array($ch,$curlOpts);
        return $ch;
    }

    /**
     * Sends a request and processes the response.
     * 
     * Assumes that the curl handle is configured properly, and that the
     * response will be JSON.
     */
    public static function send($ch) {
        $response = curl_exec($ch);
        $code = (int) curl_getinfo($ch,CURLINFO_HTTP_CODE);
        if($code === 0) {
            throw new Api2Exception("Connection error: Failed to open ".
                "connection to API at $baseUrl",
                self::ERR_CONNECTION,$ch,$response);
        }
        if(!($responseData = json_decode($response,1)) && $code != 204) {
            throw new Api2Exception("Connection or internal server error: ". 
                "Partial, broken or empty content from API at $baseUrl. ".
                "The content was: \n\n$response",
                self::ERR_SERVER,$ch,$response);
        }
        switch(floor($code/100)) {
            case 2:
            case 3:
                return $responseData;
            case 4:
                throw new Api2Exception("Client error: ".$responseData['message'], 
                    $code,$ch,$response);
            case 5:
                throw new Api2Exception("Server error: ".$responseData['message'],
                    $code,$ch,$response);
        }
        return $responseData; 
    }

    /**
     * Constructor.
     * 
     * A session and class are chosen.
     */
    public function __construct($_class,$_session='default') {
        $this->_class = $_class;
        $this->_session = $_session;
    }

    /**
     * "Magic" get method.
     * 
     * - Returns explicitly declared properties first if they exist.
     * - Calls a getter function if one is declared
     * - Uses {@link getAttribute} for everything else 
     */
    public function __get($name) {
        if(property_exists($this,$name)) {
            return parent::__get($name);
        } else if(method_exists($this,$getter = 'get'.ucfirst($name)) &&
                $name != 'attribute') {
            return $this->$getter();
        } else {
            return $this->getAttribute($name);
        }
    }

    /**
     * Setting overloading
     * 
     * Sets an attribute if one exists; otherwise uses the default 
     * setting method.
     */
    public function __set($name,$value) {
        if(!$this->setAttribute($name,$value)) {
            parent::__set($name,$value);
        }
    }
  
    /**
     * Finds all instances of a model matching given attributes.
     */
    public function findAllByAttributes($attributes) {
        $ch = self::curlHandle("{$this->_getClass}?".
            http_build_query($attributes,'','&'),'GET',$this->_session);
        $data = self::send($ch);
        $models = array();
        foreach($data as $record) {
            $model = new Api2Model($this->_class,$this->_session);
            $model->attributes = $record;
            $models[] = $model;
        }
        return $models;
    }

    /**
     * Finds a model by attributes
     */
    public function findByAttributes($attributes) {
        $urlArgs = array();
        foreach($attributes as $name=>$value) {
            $urlArgs[] = "$name=".rawurlencode($value);
        }
        $ch = self::curlHandle("{$this->_class}/by:".implode(';',$urlArgs).
            '.json?_useFirst=1','GET',$this->_session);
        return $this->_populateFromAPI($ch);
    }

    /**
     * Finds the model by primary key
     */ 
    public function findByPk($id) {
        $ch = self::curlHandle("{$this->_class}/$id.json",'GET',$this->_session);
        return $this->_populateFromAPI($ch);
    }

    /**
     * Returns the value of a stored attribute
     */ 
    public function getAttribute($name,$value) {
        if($this->hasAttribute($name)) {
            return isset($this->_attributes[$name])
                ? $this->_attributes[$name] 
                : null;
        }
        throw new Api2Exception("Error: model class {$this->_class} in session ".
            "{$this->_session} has no attribute \"$name\"");
    }

    /**
     * Returns an array with names of model attributes
     */
    public function getAttributeNames() {
        if(!isset(self::$_attributeNames[$this->_session][$this->_class])) {
            if(!isset(self::$_attributeNames[$this->_session])) {
                self::$_attributeNames[$this->_session] = array();
            }
            self::$_attributeNames[$this->_session][$this->_class] = 
                array_keys($this->getFields());
        }
        return self::$_attributeNames[$this->_session][$this->_class];
    }

    /**
     * Magic getter for the attributes array.
     */
    public function getAttributes() {
        if(!isset($this->_attributes)) {
            $this->_attributes = array_fill_keys(
                $this->getAttributeNames(),
                null);
        }
        return $this->_attributes;
    }

    /**
     * Magic getter for field permissions metadata.
     */
    public function getFieldPermissions() {
        if(!isset(self::$_fieldPermissions[$this->_session][$this->_class])) {
            if(!isset(self::$_fieldPermissions[$this->_session])) {
                self::$_fieldPermissions[$this->_session] = array();
            }
            $ch = self::curlHandle("{$this->_class}/fieldPermissions.json",'GET',$this->_session);
            self::$_fieldPermissions[$this->_session][$this->_class] = self::send($ch);
        }
        return self::$_fieldPermissions[$this->_session][$this->_class];
    }

    /**
     * Retrieves fields metadata for the current model.
     */
    public function getFields() {
        if(!isset(self::$_fields[$this->_session][$this->_class])) {
            if(!isset(self::$_fields[$this->_session])) {
                self::$_fields = array();
            }
            $ch = self::curlHandle("{$this->_class}/fields",'GET',
                $this->_session);
            $fields = self::send($ch);
            $fieldsByName = array();
            foreach($fields as $field) {
                $fieldsByName[$field['fieldName']] = $field;
            }
            self::$_fields[$this->_session][$this->_class] =
                $fieldsByName;
        }
        return self::$_fields[$this->_session][$this->_class];
    }

    /**
     * Returns true or false based on whether the current model has 
     * a given attribute.
     */
    public function hasAttribute($name) {
        $fields = $this->getFields();
        return isset($fields[$name]);
    }

    /**
     * Save data, whether new or existing
     */
    public function save() {
        $id = $this->getAttribute('id');
        if($id != null) {
            // Update existing record
            $method = 'PUT';
            $uri = "{$this->_class}/$id.json";
        } else {
            // Create new record
            $method = 'POST';
            $uri = "{$this->_class}";
        }
        $ch = self::curlHandle($uri,$method,$this->_session);
        $this->attributes = self::send($ch);
    }

    /**
     * Deletes the record via API.
     */
    public function delete() {
        $id = $this->getAttribute('id');
        if($id==null) {
            throw new Api2Exception('ID cannot be null when calling delete().');
        }
        $ch = self::curlHandle("{$this->_class}/$id.json",
            "DELETE",$this->_session);
        self::send($ch);
    }

    /**
     * Sets a named attribute
     */
    public function setAttribute($name,$value) {
        if($this->hasAttribute($name)) {
            $this->_attributes[$name] = $value;
            return true;
        } 
        return false;
    }

    /**
     * Attribute setter
     */
    public function setAttributes(array $value) {
        $this->_attributes = $value;
    }

    /**
     * Obtains a model record from a cURL handle
     * 
     * Populates and returns a new instance with attributes.
     */
    private function _populateFromAPI($ch) {
        try {
            $data = self::send($ch);
            $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        } catch (Api2Exception $e) {
            if($code = $e->getCode() != 404) {
                throw $e;
            }
            return null;
        }
        if(floor($code/100) == 3) {
            throw new Api2Exception("Redirection in effect.",$code,$ch,$data);
        }
        $model = new Api2Model($this->_class,$this->_session);
        $model->attributes = $data;
        return $model;
    }
}


/**
 * Exception class for API request errors.
 */
class Api2Exception extends Exception {

    /**
     * Curl handle that was used in the API request.
     * @var resource
     */
    public $ch = null;

    /**
     * Response data
     */
    public $response = null;

    /**
     * Constructor override.
     *
     * @param string $message The exception's message
     * @param integer $code Code (typically a HTTP status)
     * @param resource $ch Curl handle
     * @param string $response The response data 
     * @param Exception $previous Previously thrown exception, if any
     */
    public function __construct($message="",$code=0,$ch=null,$response=null,$previous=null) {
        parent::__construct($message,$code,$previous);
        $this->ch = $ch;
        $this->response = $response;
    }
}
