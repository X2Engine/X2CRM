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




class X2Html extends CHtml {

    /**
     * Can be called to guarantee safety of attribute value before inserting into markup. 
     * Far more restrictive than it needs to be, but useful in cases where a small character set
     * is expected.
     */
     // uncomment if needed
    public static function sanitizeAttribute ($value) {
        // overly restrictive, but safe, regex. 
        return preg_replace ("/[^a-zA-Z0-9-.]/", '', $value);
    }

    public static function clearfix () {
        return '<span class="clearfix"></span>';
    }

    public static function openBodyTag ($preferences, array $htmlOptions = array ()) {
        $cs = Yii::app()->clientScript;
        $baseUrl = $cs->baseUrl;
        $fullscreen = $cs->fullscreen;
        $style = '';
        $classes = 'enable-search-bar-modes';
        $noBorders = false;
        if ($preferences != null && $preferences['backgroundColor'])
            $style .= 'background-color:#'.$preferences['backgroundColor'].';';

        if ($preferences != null && $preferences['backgroundImg']) {
            if (Yii::app()->user->isGuest) {
                $media = Media::model ()->findByAttributes (array (
                    'id' => $preferences['backgroundImg']
                ));
                if ($media) {
                    $mediaUrl = $media->getPublicUrl ();
                }
            } else {
                $mediaUrl = $baseUrl.Yii::app()->request->scriptUrl.'/media/getFile/'.
                    $preferences['backgroundImg'];
            }
             
            if (isset ($mediaUrl)) {
                $style .= 'background-image:url('.$mediaUrl.');';
                $classes .= ' custom-background-image';
            }

            switch($bgTiling = $preferences['backgroundTiling']) {
                case 'repeat-x':
                case 'repeat-y':
                case 'repeat':
                    $style .= 'background-repeat:'.$bgTiling.';';
                    break;
                case 'center':
                    $style .=  'background-repeat:no-repeat;background-position:center center;';
                    break;
                case 'stretch':
                default:
                    $style .=  'background-attachment:fixed;background-size:cover;';
                    $noBorders = true;
            }
        }
        if($noBorders) $classes .= ' no-borders'; 
        if($fullscreen) $classes .= ' no-widgets'; else $classes .=  ' show-widgets';
        if(AuxLib::getLayoutType () !== 'responsive') $classes .=  ' disable-mobile-layout'; 
        $classes .= ' not-mobile-body';

        $htmlOptions = self::mergeHtmlOptions (array (
            'class' => $classes,
            'style' => $style
        ), $htmlOptions);
        return self::openTag ('body', $htmlOptions);
    }

    public static function renderPhoneLink ($phoneNumber) {
        // spaces can't be used as visual separators (see http://tools.ietf.org/html/rfc3966)
        $formattedNumber = preg_replace ('/ /', '', $phoneNumber);
        return '<a href="tel:+'.$formattedNumber.'">'.CHtml::encode ($phoneNumber) .'</a>';
    }

    public static function renderEmailLink ($emailAddress) {
        return '<a href="mailto:'.$emailAddress.'">'.CHtml::encode ($emailAddress) .'</a>';
    }

    public static function renderSkypeLink ($skypeUser) {
        $skypeUser = (array) $skypeUser;
        $id = uniqid ();
        echo 
            '<div id="'.$id.'">
                <script>
                    Skype.ui ({
                        name: "dropdown",
                        element: "'.$id.'",
                        participants: '.CJSON::encode ($skypeUser).'
                    });
                </script>
            </div>';
    }

    /**
     * renders a loading gif at the center of the screen (to center it within an element, add 
     * position: relative to one of its parents.
     * @param array $htmlOptions
     */
    public static function loadingIcon (array $htmlOptions=array ()) {
        $htmlOptions = self::mergeHtmlOptions (array (
            'class' => 'x2-loading-icon load8 full-page-loader x2-loader',
        ), $htmlOptions);
        $html = '';
        $html .= self::openTag ('div', $htmlOptions);
        $html .= self::openTag ('div', array ('class' => 'loader'));
        $html .= self::closeTag ('div');
        $html .= self::closeTag ('div');
        return $html;
    }

    /**
     * Safeguarded override of {@link CHtml::encode()}
     * 
     * Converts the text's encoding to avoid "invalid multibyte sequence" errors
     */
    public static function encode($text) {
        return parent::encode(Formatter::mbSanitize($text));
    }

    /**
     * Returns html for error, success, and notice flashes. 
     */
    public static function getFlashes () {
        if (Yii::app()->user->hasFlash('error')) {
            echo "<div class='flash-error'>";
            echo Yii::app()->user->getFlash('error');
            echo "</div>";
        }
        if (Yii::app()->user->hasFlash('notice')) {
            echo "<div class='flash-notice'>";
            echo Yii::app()->user->getFlash('notice');
            echo "</div>";
        }
        if (Yii::app()->user->hasFlash('success')) {
            echo "<div class='flash-success'>";
            echo Yii::app()->user->getFlash('success');
            echo "</div>";
        }
    }

    /**
     * Provides a way to add a '?' with a tooltip to show users how to use the app
     * 
     * @param type $text
     * @param type $superScript
     * @param type $id
     * @param type $brackets
     * @param type $encode
     * @return type
     */
    public static function hint(
        $text, $superScript = true, $id = null, $brackets = false, $encode = true){

        $text = Formatter::mbSanitize ($text);
        $htmlOptions = array(
            'class' => 'x2-hint x2-question-mark fa fa-question-circle',
            'title' => $encode ? htmlentities($text, ENT_QUOTES, Yii::app()->charset) : $text,
        );
        if($id !== null){
            $htmlOptions['id'] = $id;
        }
        return parent::tag('span', $htmlOptions, '');
    }

    public static function hint2 ($title, $htmlOptions=array (), $encode=true) {
        $htmlOptions = self::mergeHtmlOptions (array(
            'class' => 'x2-hint x2-question-mark fa fa-question-circle',
            'title' => $encode ? self::encode ($title) : $title
        ), $htmlOptions);
        return parent::tag('span', $htmlOptions, '');
    }

    public static function mergeHtmlOptions ($optsA, $optsB) {
        $opts = array ();
        if (isset ($optsA['class']) && isset ($optsB['class'])) {
            $opts['class'] = $optsA['class'].' '.$optsB['class'];
            unset ($optsA['class']);
            unset ($optsB['class']);
        }
        $opts = array_merge ($opts, $optsA, $optsB);
        return $opts;
    }

    /**
     * Generates a settings button 
     * @param string $alt the image alt
     * @param array $htmlOptions options to be applied to the settings button
     * @return string 
     */
    public static function settingsButton ($alt='', $htmlOptions) {
        if (!isset ($htmlOptions['class'])) {
            $htmlOptions['class'] = '';
        }
        $htmlOptions['class'] .= ' fa-lg fa fa-cog x2-settings-button';

        return self::openTag('span', $htmlOptions).'</span>';
    }

    /**
     * Renders main content page title 
     * @param string $pageTitle 
     */
    public static function renderPageTitle ($pageTitle, array $htmlOptions=array ()) {
        if (isset ($htmlOptions['class'])) {
            $htmlOptions['class'] .= ' page-title';
        } else {
            $htmlOptions['class'] = 'page-title';
        }
        echo self::tag('div', $htmlOptions, self::tag ('h2', array (), $pageTitle));
    }

    /**
     * Modified so that overridden listOptions method is called
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
     */
	public static function dropDownList($name,$select,$data,$htmlOptions=array())
	{
        /* x2modstart */ 
        if (isset($htmlOptions['class']) ) {
            $htmlOptions['class'] .= ' x2-select';
        } else {
            $htmlOptions['class'] = 'x2-select';
        }
        /* x2modend */ 

		$htmlOptions['name']=$name;

		if(!isset($htmlOptions['id']))
			$htmlOptions['id']=self::getIdByName($name);
		elseif($htmlOptions['id']===false)
			unset($htmlOptions['id']);


		self::clientChange('change',$htmlOptions);
        /* x2modstart */ 
        // use late static binding to call modified listOptions method
		$options="\n".static::listOptions($select,$data,$htmlOptions);
        /* x2modend */ 
		$hidden='';

		if(!empty($htmlOptions['multiple']))
		{
			if(substr($htmlOptions['name'],-2)!=='[]')
				$htmlOptions['name'].='[]';

			if(isset($htmlOptions['unselectValue']))
			{
				$hiddenOptions=isset($htmlOptions['id']) ? array('id'=>self::ID_PREFIX.$htmlOptions['id']) : array('id'=>false);
				$hidden=self::hiddenField(substr($htmlOptions['name'],0,-2),$htmlOptions['unselectValue'],$hiddenOptions);
				unset($htmlOptions['unselectValue']);
			}
		}
		// add a hidden field so that if the option is not selected, it still submits a value
		return $hidden . self::tag('select',$htmlOptions,$options);
	}

    /**
     * Modified so that overridden listOptions method is called
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
     */
	public static function activeDropDownList($model,$attribute,$data,$htmlOptions=array())
	{
        /* x2modstart */ 
        if (isset($htmlOptions['class']) ) {
            $htmlOptions['class'] .= ' x2-select';
        } else {
            $htmlOptions['class'] = 'x2-select';
        }
        /* x2modend */ 

		self::resolveNameID($model,$attribute,$htmlOptions);
		$selection=self::resolveValue($model,$attribute);
        /* x2modstart */ 
        // use late static binding to call modified listOptions method
		$options="\n".static::listOptions($selection,$data,$htmlOptions);
        /* x2modend */ 
		self::clientChange('change',$htmlOptions);

		if($model->hasErrors($attribute))
			self::addErrorCss($htmlOptions);

		$hidden='';
		if(!empty($htmlOptions['multiple']))
		{
			if(substr($htmlOptions['name'],-2)!=='[]')
				$htmlOptions['name'].='[]';

			if(isset($htmlOptions['unselectValue']))
			{
				$hiddenOptions=isset($htmlOptions['id']) ? array('id'=>self::ID_PREFIX.$htmlOptions['id']) : array('id'=>false);
				$hidden=self::hiddenField(substr($htmlOptions['name'],0,-2),$htmlOptions['unselectValue'],$hiddenOptions);
				unset($htmlOptions['unselectValue']);
			}
		}
		return $hidden . self::tag('select',$htmlOptions,$options);
	}

    /**
     * Modified to specially handle opt groups
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
     */
    public static function listOptions($selection,$listData,&$htmlOptions)
	{
		$raw=isset($htmlOptions['encode']) && !$htmlOptions['encode'];
		$content='';
		if(isset($htmlOptions['prompt']))
		{
			$content.='<option value="">'.strtr($htmlOptions['prompt'],array('<'=>'&lt;','>'=>'&gt;'))."</option>\n";
			unset($htmlOptions['prompt']);
		}
		if(isset($htmlOptions['empty']))
		{
			if(!is_array($htmlOptions['empty']))
				$htmlOptions['empty']=array(''=>$htmlOptions['empty']);
			foreach($htmlOptions['empty'] as $value=>$label)
				$content.='<option value="'.self::encode($value).'">'.strtr($label,array('<'=>'&lt;','>'=>'&gt;'))."</option>\n";
			unset($htmlOptions['empty']);
		}

		if(isset($htmlOptions['options']))
		{
			$options=$htmlOptions['options'];
			unset($htmlOptions['options']);
		}
		else
			$options=array();

		$key=isset($htmlOptions['key']) ? $htmlOptions['key'] : 'primaryKey';
		if(is_array($selection))
		{
			foreach($selection as $i=>$item)
			{
				if(is_object($item))
					$selection[$i]=$item->$key;
			}
		}
		elseif(is_object($selection))
			$selection=$selection->$key;

		foreach($listData as $key=>$value)
		{
			if(is_array($value))
			{
                /* x2modstart */ 
                // leave out optgroup label if key is empty string
                if ($key !== '') {
                    $content.='<optgroup label="'.($raw?$key : self::encode($key))."\">\n";
                }
                /* x2modend */ 
				$dummy=array('options'=>$options);
				if(isset($htmlOptions['encode']))
					$dummy['encode']=$htmlOptions['encode'];
				$content.=self::listOptions($selection,$value,$dummy);
                /* x2modstart */ 
                if ($key !== '') {
				    $content.='</optgroup>'."\n";
                }
                /* x2modend */ 
			}
			else
			{
				$attributes=array('value'=>(string)$key,'encode'=>!$raw);
				if(!is_array($selection) && !strcmp($key,$selection) || is_array($selection) && in_array($key,$selection))
					$attributes['selected']='selected';
				if(isset($options[$key]))
					$attributes=array_merge($attributes,$options[$key]);
				$content.=self::tag('option',$attributes,$raw?(string)$value : self::encode((string)$value))."\n";
			}
		}

		unset($htmlOptions['key']);

		return $content;
	}

    public static function activeMultiTypeAutocomplete (
        CModel $model, $typeAttribute, $idAttribute, $options, array $config = array ()) {

        return Yii::app()->controller->widget ('MultiTypeAutocomplete', array_merge (array (
            'model' => $model,
            'selectName' => $typeAttribute,
            'hiddenInputName' => $idAttribute,
            'options' => $options,
        ), $config), true);

    }

    /**
     * @param CModel $model 
     * @param string $attribute 
     * @param array (optional) $htmlOptions 
     * @return string
     */
    public static function activeDatePicker (
        CModel $model, $attribute, array $htmlOptions = array (), $mode='date', 
        array $options = array ()) {

        $options = array_merge (array(
            'dateFormat' => Formatter::formatDatePicker(),
            'changeMonth' => true,
            'changeYear' => true,
        ), $options);

        ob_start ();
        ob_implicit_flush(false);
        Yii::import ('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');

        $model->$attribute = Formatter::formatDateTime ($model->$attribute);
        $renderWidget = function () use ($model, $attribute, $htmlOptions, $mode, $options) {  
            Yii::app()->controller->widget('CJuiDateTimePicker', array(
                'model' => $model, 
                'attribute' => $attribute, 
                'mode' => $mode, 
                'options' => $options,
                'htmlOptions' => $htmlOptions,
                'language' => (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage(),
            ));
        };
        if (Yii::app()->controller->isAjaxRequest ()) { // process output if this is an ajax request
            X2Widget::ajaxRender ($renderWidget);
        } else {
            $renderWidget ();
        }
        return ob_get_clean();
    }

    /**
     * Create a <ul> tag with <li> items from an array 
     * @param $listContents array of list item attributes to add to the list
     * The 'content' key in the array will be put on the inside of the tag rather 
     * than be an attribute
     * @param $htmlOptions html attributes to be given to the <ul> tag
     * @param $itemClass a class to add to all of the list Items 
     */
    public static function ul($listContents, $htmlOptions=array(), $itemClass='') {
        $html = '';
        $html .= self::openTag('ul', $htmlOptions);
        foreach($listContents as $item) {
            if( empty($item['content'] ) ) {
                $content = '';
            } else {
                $content = $item['content'];
                unset( $item['content'] );
            }

            if( empty($item['class'] ) ) {
                $item['class'] = '';
            }

            $item['class'].= " $itemClass";

            $html .= self::tag('li', $item, $content);
        }

        $html .=  '</ul>';

        return $html;
    }   

    public static function popUpDropDown ($list, $htmlOptions=array()) { 
        if (isset($htmlOptions['class'])) {
            $htmlOptions['class'] .= ' popup-dropdown-menu';
        } else {
            $htmlOptions['class'] = 'popup-dropdown-menu';
        }
        
        $ul = self::ul ($list);

        return self::tag('div', $htmlOptions, $ul);


    }



    /**
     * Create an font awesome icon tag
     * @param string $iconClass Name of icon such as 'fa-edit' or 'copy' (with or without fa)
     * @param array $htmlOptions extra options to be passed to the tag
     * @param string $optional content to be passed inside of the tag (not recommended)
     * @return string generated html content
     */
    public static function fa($iconClass, $htmlOptions = array(), $content=' ', $tag='i') {
        if (!isset($htmlOptions['class'])) {
            $htmlOptions['class'] = '';
        }

        // Prepend with fa- if not there
        if (!preg_match('/^fa-/', $iconClass)) {
            $iconClass = 'fa-'.$iconClass;
        }
        
        $htmlOptions['class'] .= " fa $iconClass";
        return self::tag($tag, $htmlOptions, $content);
    }

    /**
     * Create an custom icon tag
     * @param string $iconClass name of icon such as 'contact' or 'activity'
     * @param array $htmlOptions extra options to be passed to the tag
     * @param string $optional content to be passed inside of the tag (not recommended)
     * @return string generated html content
     */
    public static function x2icon($iconClass, $htmlOptions = array(), $content=' ') {
        if (!isset($htmlOptions['class'])) {
            $htmlOptions['class'] = '';
        }
        
        $htmlOptions['class'] .= " icon-$iconClass";
        return self::tag('i', $htmlOptions, $content);
    }


    public static function IEBanner($ver = 9, $echo = true) {
        if (AuxLib::getIEVer() >= $ver) {
            return false;
        }
        
        $htmlOptions = array(
            'class' => 'ie-banner'
        );

        $message = Yii::t('app', 
            'This feature does not support your version of Internet Explorer');
        
        if ($echo) {
            echo self::tag ('h2', $htmlOptions, Yii::t('app', $message));
            return true;
        } else {
            return self::tag ('h2', $htmlOptions, Yii::t('app', $message));
        }

    }

    /*********************************
    * Record view Page title items
    * (I believe these belong in a record-view shared class)
    ********************************/
    public static function emailFormButton() {
        return CHtml::link(
            '', 
            '#',
        array(
            'class' => 'x2-button icon right email',
            'title' => Yii::t('app', 'Open email form'),
            'onclick' => 'toggleEmailForm(); return false;'
        ));
    }

    public static function editRecordButton($model) {
        return CHtml::link('', array(
                'update', 
                'id' => $model->id
            ), array(
                'class' => 'x2-button icon edit right',
                'title' => Yii::t('app', "Edit")
        )); 
    }

    /**
     * Deprecated. Inline buttons are in the form now. 
     */
    public static function inlineEditButtons() {
        $html = '';
        $html .= CHtml::link( 
            X2Html::fa('fa-check-circle fa-lg').Yii::t('app', 'Confirm'),
            '#',
             array(
                'id'=>'inline-edit-save',
                'class'=>'x2-button icon right inline-edit-button highlight',
                'style'=>'display:none;',
                'title'=> Yii::t('app', 'Confirm change to fields')
            )
        );

        $html .= CHtml::link(
            X2Html::fa('fa-times fa-lg').'  '.Yii::t('app', 'Cancel'),
            '#',
            array(
                'id'=>'inline-edit-cancel',
                'class'=>'x2-button icon right inline-edit-button',
                'style'=>'display:none;',
                'title'=> Yii::t('app', 'Cancel changes to fields')
            )
        );

        return $html;
    }

    public static function addErrorCss(&$htmlOptions) {
        return parent::addErrorCss ($htmlOptions);
    }

    public static function quoteIcon () {
        // return X2Html::x2icon('quotes');
        return "
            <span class='fa-stack quote-icon'>
                <i class='fa fa-file-o fa-stack-1x fa-flip-horizontal'></i>
                <i class='fa fa-pencil fa-stack-1x'></i>
            </span>";
    }

    public static function activeRichTextArea (
        CModel $model, $attribute, array $htmlOptions=array ()) {

        Yii::app()->clientScript->registerPackage('emailEditor');

        if (isset ($htmlOptions['class'])) {
            $htmlOptions['class'] .= ' x2-rich-textarea';
        } else {
            $htmlOptions['class'] = 'x2-rich-textarea';
        }

        if (!isset ($htmlOptions['width'])) {
            $htmlOptions['width'] = '725px';
        }
        if (!isset ($htmlOptions['height'])) {
            $htmlOptions['height'] = '125px';
        }

        return CHtml::activeTextArea ($model, $attribute, $htmlOptions);
    }

    public static function activeCodeEditor (
        CModel $model, $attribute, array $htmlOptions = array ()) {

        if (isset ($htmlOptions['class'])) {
            $htmlOptions['class'] .= ' x2-code-editor';
        } else {
            $htmlOptions['class'] = 'x2-code-editor';
        }

        Yii::app()->clientScript->registerPackage('CodeMirrorJS');
        return CHtml::activeTextArea ($model, $attribute, $htmlOptions);
    }

    public static function minimizeButton (
        array $htmlOptions=array (), $hideableSelector=null, $left=true, $defaultOpen=false) {

        $orientation = $left ? 'left' : 'right';
        if ($hideableSelector) {
            $js = "$('$hideableSelector').toggle ();";
        } else {
            $js = "";
        }
        $html = 
            X2Html::fa('fa-caret-'.$orientation, array_merge (array (
                'style' => !$defaultOpen ? '' : 'display: none;',
                'onClick' => '$(this).hide (); $(this).next ().show ();'.$js.
                    '; event.stopPropagation (); event.preventDefault ();',
            ), $htmlOptions)).
            X2Html::fa('fa-caret-down', array_merge (array (
                'style' => $defaultOpen ? '' : 'display: none;',
                'onClick' => 
                    '$(this).hide (); $(this).prev ().show ();'.$js.
                    '; event.stopPropagation (); event.preventDefault ();',
            ), $htmlOptions));
        return $html;
    }
    
    /**
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
     */
    public static function css($text, $media='', array $htmlOptions=array ())
    {
        if($media!=='')
            $media=' media="'.$media.'"';
        return "<style ".self::renderAttributes ($htmlOptions)
            ." type=\"text/css\"{$media}>\n/*<![CDATA[*/\n{$text}\n/*]]>*/\n</style>";
    }

    public static function dynamicDate ($date) {
        return '<span title="'.
            CHtml::encode (Formatter::formatCompleteDate ($date)).'">'.
            CHtml::encode (Formatter::formatDateDynamic ($date)).
        '</span>';
    }

    /**
     * Echos a divider useful for custom forms. Example usage in webform creator
     * @param  string $width  width of divider
     * @param  string $margin Margin on either side of element
     */
    public static function divider($width='100%', $margin='15px') {
        return "<div class='x2-divider' style='width:100%;max-width:$width; margin-top: $margin; margin-bottom: $margin'></div>";
    }

    /**
     * Echos the default avatar for a profile
     */
    public static function defaultAvatar ($size='') {
        return self::x2icon ('profile-large', array(
            'style' => "font-size: ${size}px;",
            'class' => 'default-avatar', 
        ));
    }

    /**
     * Returns an input with the CSRF Token
     */
    public static function csrfToken(){
        return self::hiddenField('YII_CSRF_TOKEN', Yii::app()->request->csrfToken);
    }

    public static function logo ($type, array $htmlOptions = array ()) {
        assert (in_array ($type, array ('mobile', 'menu', 'login_white', 'login_black', 'about')));

        $logoName = $type.'_logo';
        $html = '';
        if (in_array ($type, array ('menu')) && Auxlib::isIE8() ||
            in_array ($type, array ('login_white', 'login_black', 'mobile')) && Auxlib::isIE()) {
            $htmlOptions = self::mergeHtmlOptions (array (
                'class' => $logoName,
            ), $htmlOptions);
            $filename = $logoName.'.png';
            $html .= CHtml::image(
                Yii::app()->request->baseUrl.'/images/'.$filename, 
                Yii::app()->settings->appName,
                $htmlOptions);
        } else {
            $htmlOptions = self::mergeHtmlOptions (array (
                'class' => 'icon-x2-logo-square '.$logoName,
            ), $htmlOptions);
            $html .= CHtml::tag('span', $htmlOptions, ' ');
        }
        return $html;
    }

    public static function x2ActivePasswordField (
        CModel $model, $attr, array $htmlOptions=array (), $enableVisibilityToggle=false) {

        static $counter = 0;

        $html = '';
        if ($enableVisibilityToggle) {
            $id = isset ($htmlOptions['id']) ? 
                $htmlOptions['id'] : 'X2Html_'.__FUNCTION__.'_'.$counter++;
                $htmlOptions['id'] = $id;
        }
        $html .= CHtml::activePasswordField($model, $attr, $htmlOptions);
        if ($enableVisibilityToggle) {
            $html .= '<div class="password-visibility-toggle" 
                title="'.CHtml::encode (Yii::t('app', 'Toggle password visibility')).'">';
            $html .= self::fa ('eye');
            $html .= self::fa ('eye-slash', array ('style' => 'display:none;'));
            $html .= '</div>';
            Yii::app()->clientScript->registerScript('activePasswordField'.$id,"
            ;(function () {
                $('#$id').siblings ('.password-visibility-toggle').on ('click', function (elem) {
                    $(this).children ().toggle ();
                    var input$ = $('#$id');
                    input$.attr ('type', input$.attr ('type') === 'password' ? 'text' : 'password');
                });
            }) ();
            ");
        }
        return $html;
    }

    public static function orderedList (array $items, array $htmlOptions=array ()) {
        return self::_list (true, $items, $htmlOptions);
    }

    public static function unorderedList (array $items, array $htmlOptions=array ()) {
        return self::_list (false, $items, $htmlOptions);
    }

    /**
     * Creates fragment targets which are offset by a little more than the height of the top menu. 
     * Allows you to link inside documents without having the top of the section you're linking to 
     * cut off.
     */
    public static function fragmentTarget ($id) {
        return self::tag (
            'span', 
            array ('class' => 'fragment-target-outer'), 
            self::tag ('span', array ('class' => 'fragment-target', 'id' => $id)));
    }

    private static function _list ($ordered=false, array $items, array $htmlOptions=array ()) {
        $html = '';
        $html .= self::openTag ($ordered ? 'ol' : 'ul', $htmlOptions);
        foreach ($items as $item) {
            $html .= self::tag ('li', array (), $item);
        }
        $html .= self::closeTag ($ordered ? 'ol' : 'ul');
        return $html;
    }

    public static function encodeArray ($arr) {
        return array_map (function ($elem) {
            return CHtml::encode ($elem);
        }, $arr);
    }

    public static function resolveId (CModel $model, $attr) {
        $htmlOptions = array ();
        self::resolveNameId ($model, $attr, $htmlOptions);
        return $htmlOptions['id'];
    }

}
