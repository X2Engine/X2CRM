<?php
/**
 * YiiChatWidget 
 *
 *	Please refer to README about details.
 *
 *
 * @uses CWidget
 * @version 1.0 
 * @author Christian Salazar <christiansalazarh@gmail.com> 
 * @license FREE BSD
 */
class YiiChatWidget extends CWidget {

	public	$chat_id;	// chat main identificator
	public	$identity;	// ID, can be: Yii::app()->user->id
	public	$selector;	// jQuery selector (holder)

	public	$sendButtonText='Send';
	public	$onError;
	public	$onSuccess;
	public  $defaultController='/site';	
	public	$minPostLen=2;
	public	$maxPostLen=140;
	public  $model; // a model instance, it must implements IYiiChat
	public  $timerMs = 5000;
	public  $data;	// public data passed to the model instance

	public	$myOwnPostCssStyle;		// css style names
	public	$othersPostCssStyle;	//

	private $_action;
	private $_baseUrl;

	public function init(){
		parent::init();
		$this->_prepareAssets();
		if($this->onError==null || $this->onError=='')
			$this->onError = new CJavaScriptExpression("function(err, txt){}");
		if(!($this->onError instanceof CJavaScriptExpression))
			throw new Exception("onError must be a CJavaScriptExpression");	
		if($this->onSuccess==null || $this->onSuccess=='')
			$this->onSuccess = new CJavaScriptExpression("function(code, txt, post_id){}");
		if(!($this->onSuccess instanceof CJavaScriptExpression))
			throw new Exception("onSuccess must be a CJavaScriptExpression");	
	}

	public function run(){
		$_interfaceName='IYiiChat';
		if($_impl = class_implements($this->model))
			if(!isset($_impl[$_interfaceName]))
				throw new Exception("The model instance passed by "
					."argument to this widget must implements IYiiChat interface");
		// this 'dummy' argument is required to provide urlmanager support...
		$this->_action = array($this->defaultController.'/yiichat','dummy'=>'@');
		$options = CJavaScript::encode(array(
			'selector'=>$this->selector,
			'chat_id'=>$this->chat_id,
			'identity'=>$this->identity,
			'sendButtonText'=>$this->sendButtonText,
			'onError'=>$this->onError,
			'onSuccess'=>$this->onSuccess,
			'minPostLen'=>$this->minPostLen,
			'maxPostLen'=>$this->maxPostLen,
			'action'=>CHtml::normalizeUrl($this->_action),
			'timerMs'=>$this->timerMs,
			'myOwnPostCssStyle'=>$this->myOwnPostCssStyle,
			'othersPostCssStyle'=>$this->othersPostCssStyle,
		));
		$s = Yii::app()->session;
		$s[$this->chat_id.'_model'] = $this->model;
		$s[$this->chat_id.'_data'] = $this->data;
		$var_id = rand(1000,9999);
		Yii::app()->getClientScript()->registerScript("yii_chat_script_".$var_id
		,"	var chat_{$var_id} = new YiiChat({$options});
			chat_{$var_id}.run();
		");
	}// end run()

	public function _prepareAssets(){
		$localAssetsDir = dirname(__FILE__) . '/assets';
		$this->_baseUrl = Yii::app()->getAssetManager()->publish(
				$localAssetsDir);
        $cs = Yii::app()->getClientScript();
        $cs->registerCoreScript('jquery');
		foreach(scandir($localAssetsDir) as $f){
			$_f = strtolower($f);
			if(strstr($_f,".swp"))
				continue;
			if(strstr($_f,".js"))
				$cs->registerScriptFile($this->_baseUrl."/".$_f);
			if(strstr($_f,".css"))
				$cs->registerCssFile($this->_baseUrl."/".$_f);
		}
	}

	public function runAction($action, $data){
		$chat_id = $this->_getPost('chat_id',null);
		$identity = $this->_getPost('identity',null);
		$text = $this->_getPost('text');
		$s = Yii::app()->session;
		$model = $s[$chat_id.'_model'];
		$data = $s[$chat_id.'_data'];
		if(($action == 'sendpost') && $identity && $chat_id){
			header("Content-type: application/json");
			if($post = $model->yiichat_post($chat_id, $identity, $text, $data)){
				if(!isset($post['chat_id']))
					$post['chat_id']=$chat_id;
				if(!isset($post['identity']))
					$post['identity']=$identity;
				if(!isset($post['post_identity']))
					$post['post_identity']=$identity; // must be the same
				echo CJSON::encode($post);
			}else{
				echo CJSON::encode("REJECTED");
			}
		}
		if(($action == 'init') && $identity && $chat_id){
			$posts = $model->yiichat_list_posts($chat_id, $identity, -1, $data);
			if($posts==null)
				$posts = array();
			$data = array('chat_id'=>$chat_id, 'identity'=>$identity,
				'posts'=>$posts);
			header("Content-type: application/json");
			echo CJSON::encode($data);
		}
		if(($action == 'timer') && $identity && $chat_id){
			$posts = $model->yiichat_list_posts(
					$chat_id, $identity, $this->_getPost('last_id'), $data);
			if($posts==null)
				$posts = array();
			$data = array('chat_id'=>$chat_id, 'identity'=>$identity,
				'posts'=>$posts);
			header("Content-type: application/json");
			echo CJSON::encode($data);
		}
	}

	private function _getPost($attr, $def=''){
		if(isset($_POST[$attr])){
			$value = trim($_POST[$attr]);
			if(($value == 'undefined') || ($value == null) || ($value==''))
				return $def;
			return $value;
		}
		else{
			return $def;
		}
	}
}
