<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of APIModel
 *
 * @author jake
 */
class APIModel {
    
    private $_user='';
    private $_userKey='';
    private $_baseUrl='';
    private $_apiKey='';
    
    public function __construct($user=null, $userKey=null, $baseUrl=null){
        $this->_user=$user;
        $this->_userKey=$userKey;
        $this->_baseUrl=$baseUrl;
    }
    
    public function authenticate(){
        
        $ccUrl = 'http://'.$this->_baseUrl.'/index.php/api/authenticate'; 
        $ccSession = curl_init($ccUrl);
        curl_setopt($ccSession,CURLOPT_POST,1);
        curl_setopt($ccSession, CURLOPT_USERPWD, 'x3engine:x3dev!!123%%');
        curl_setopt($ccSession,CURLOPT_POSTFIELDS,array('username'=>$this->_user,'userKey'=>$this->_userKey));
        curl_setopt($ccSession,CURLOPT_RETURNTRANSFER,1);
        $ccResult = curl_exec($ccSession);
        
        if(preg_match('/^[a-f0-9]{32}$/', $ccResult)){
            $this->_apiKey=$ccResult;
            return "Authentication succeeded.";
        }else{
            return "Authentication failed.";
        }
    }
    
    public function contactCreate($data=array(),$leadRouting=false){
        if(empty($this->_apiKey)){
            $this->authenticate();
        }
        $attributes=array(
			'assignedTo'=>$this->_user,
			'visibility'=>'1',
			'createDate'=>$time,
			'lastUpdated'=>$time,
			'updatedBy'=>$this->_user,
		);
        foreach($data as $field=>$value){
            $attributes[$field]=$value;
        }
        $ccUrl = 'http://'.$this->_baseUrl.'/index.php/api/create/model/Contacts'; 
        $ccSession = curl_init($ccUrl);
        curl_setopt($ccSession,CURLOPT_POST,1);
        curl_setopt($ccSession, CURLOPT_USERPWD, 'x3engine:x3dev!!123%%');
        curl_setopt($ccSession,CURLOPT_POSTFIELDS,array_merge(array('apiKey'=>$this->_apiKey,'userKey'=>$this->_userKey),$attributes));
        curl_setopt($ccSession,CURLOPT_RETURNTRANSFER,1);
        $ccResult = curl_exec($ccSession);
        return $ccResult;
    }
}

?>
