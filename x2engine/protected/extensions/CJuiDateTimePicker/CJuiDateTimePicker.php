<?php
/**
 * CJuiDateTimePicker class file.
 *
 * @author Anatoly Ivanchin <van4in@gmail.com>
 */

Yii::import('zii.widgets.jui.CJuiDatePicker');
class CJuiDateTimePicker extends CJuiDatePicker
{
	const ASSETS_NAME='/jquery-ui-timepicker-addon';
	
	public $mode='datetime';
	
	public function init()
	{
		if(!in_array($this->mode, array('date','time','datetime')))
			throw new CException('unknow mode "'.$this->mode.'"');
		if(!isset($this->language))
			$this->language=Yii::app()->getLanguage();
		return parent::init();
	}
	
	public function run()
	{
		list($name,$id)=$this->resolveNameID();

		if(isset($this->htmlOptions['id']))
			$id=$this->htmlOptions['id'];
		else
			$this->htmlOptions['id']=$id;
		if(isset($this->htmlOptions['name']))
			$name=$this->htmlOptions['name'];
		else
			$this->htmlOptions['name']=$name;

		if($this->hasModel())
			echo CHtml::activeTextField($this->model,$this->attribute,$this->htmlOptions);
		else
			echo CHtml::textField($name,$this->value,$this->htmlOptions);


		$options=CJavaScript::encode($this->options);

		$js = "jQuery('#{$id}').{$this->mode}picker($options);";

		if (isset($this->language)){
			$this->registerScriptFile($this->i18nScriptFile);
			$js = "jQuery('#{$id}').{$this->mode}picker(jQuery.extend({showMonthAfterYear:false}, jQuery.datepicker.regional['{$this->language}'], {$options}));";
		}

		$cs = Yii::app()->getClientScript();
		
		$assets = Yii::app()->getAssetManager()->publish(dirname(__FILE__).DIRECTORY_SEPARATOR.'assets');
        /* x2modstart */
		$cs->registerCssFile($assets.self::ASSETS_NAME.'.css');

        // importing a different version of timepicker
		//$cs->registerScriptFile($assets.self::ASSETS_NAME.'.js',CClientScript::POS_END);
        Yii::app()->clientScript->registerScriptFile (
            Yii::app()->getBaseUrl().'/js/jquery-ui-timepicker-addon.js', CClientScript::POS_END);
        /* x2modend */  
		
		$cs->registerScript(
            __CLASS__, 	
            $this->defaultOptions ?
                'jQuery.{$this->mode}picker.setDefaults('.
                    CJavaScript::encode($this->defaultOptions).');':
            '');
		$cs->registerScript(__CLASS__.'#'.$id, $js);

	}
}
