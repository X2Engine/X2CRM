<?php

Yii::import('application.components.util.ResponseUtil');

/**
 * Behavior class providing utilities for responding in a uniform yet also
 * context-sensitive manner. Utilizes the standalone class {@link ResponseUtil}.
 *
 * @property boolean $exitNonFatal (write-only) Sets the value of
 *  {@link ResponseUtil::$exitNonFatal}.
 * @property boolean $isConsole If true, run methods as though there's no HTTP
 *  request happening.
 * @property string $logCategory The log category to which informational output
 *  should be sent.
 * @property boolean $longErrorTrace (write-only) Sets the value of
 *  {@link ResponseUtil::$longErrorTrace}
 * @property ResponseUtil $response The response utility singleton
 * @property boolean $shutdown (write-only) Sets the value of
 *  {@link ResponseUtil::$shutdown}
 * @package application.components
 */
class ResponseBehavior extends CBehavior {

    /**
     * If true: the error handling methods of {@link ResponseUtil} should be
     * used.
     */
    public $handleErrors = false;
    
    /**
     * If true: the exception handling method
     * {@link ResponseUtil::respondWithException} should be used
     * @var type
     */
    public $handleExceptions = false;

	private $_isConsole;
    
    private $_logCategory = 'application';

    public function __construct(){
        // Establish a graceful shutdown method by default:
        ResponseUtil::$shutdown = "Yii::app()->end();";
    }

    /**
     * 
     * @param type $owner
     */
    public function attach($owner){
        parent::attach($owner);
        ResponseUtil::$includeExtraneousOutput = YII_DEBUG;
        if($this->handleErrors) {
            set_error_handler('ResponseUtil::respondWithError');
            register_shutdown_function('ResponseUtil::respondFatalErrorMessage');
        }
        if($this->handleExceptions) {
    		set_exception_handler('ResponseUtil::respondWithException');
        }

    }

    ////////////////////
    // Getter Methods //
    ////////////////////

	/**
	 * {@link isConsole}
	 * @return bool
	 */
	public function getIsConsole(){
        if(!isset($this->_isConsole)) {
            $this->_isConsole = ResponseUtil::isCli();
        }
		return $this->_isConsole;
	}

    /**
     * {@link logCategory}
     * @return type
     */
    public function getLogCategory() {
        return $this->_logCategory;
    }

	/**
	 * Returns the response utility object in use.
	 */
	public function getResponse(){
        if(!ResponseUtil::getObject()) {
            // Instantiate a new object
            new ResponseUtil();
        }
		return ResponseUtil::getObject();
	}

	/**
	 * Incorporate more properties into the response.
     * 
	 * @param array $properties
	 */
	public function mergeResponse(array $properties) {
        foreach($properties as $name => $value) {
            $this->response[$name] = $value;
        }
	}
    
    /**
     * A web-safe wrapper for {@link respond()}
     *
     * For use when logging (and in console commands, output) are needed, but
     * halting is not.
     *
     * @param string $msg Message to log/respond with
     * @param bool $error Whether an error has occurred
     * @param bool $halt If true (default) and the $level argument is "error",
     *  the application will halt after printing the error message; otherwise it
     *  will continue.
     */
    public function output($msg,$error=false) {
        Yii::log($msg,$error ? 'error' : 'trace',$this->_logCategory);
        if($this->isConsole) {
            // Perform both logging and response:
            $this->respond($msg,$error);
        }
    }

    /**
     * Wrapper method for
     * @param type $msg
     * @param type $error
     */
    public function respond($msg,$error=false) {
        ResponseUtil::respond($msg,$error);
    }

    ////////////////////
    // Setter Methods //
    ////////////////////


    /**
     * Set the default error code in {@link ResponseUtil}
     * @param type $value
     */
    public function setErrorCode($value) {
        ResponseUtil::$errorCode = $value;
    }

    /**
	 * Sets {@link ResponseUtil::$exitNonFatal}
	 * @return bool
	 */
	public function setExitNonFatal($value){
		ResponseUtil::$exitNonFatal = (bool) $value;
	}
    
	/**
	 * {@link isConsole}
	 */
	public function setIsConsole($value){
		$this->_isConsole = $value;
	}

    /**
     * {@link logCategory}
     */
    public function setLogCategory($value) {
        $this->_logCategory = $value;
    }

    /**
	 * {@link longErrorTrace}
	 */
	public function setLongErrorTrace($value){
		ResponseUtil::$longErrorTrace = (bool) $value;
	}

    public function setShutdown($value) {
        ResponseUtil::$shutdown = (bool) $value;
    }
}



?>
