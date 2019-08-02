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




Yii::import('zii.widgets.CPortlet');


/**
 * Gives a utility function to derived classes which sets up this left widgets title bar.
 * @package application.components 
 */
class LeftWidget extends CPortlet {

	/**
     * The name of the widget. This should match the name used in the layout stored in
     * the user's profile.
	 * @var string
	 */
    public $widgetName;

	/**
     * The label used in this widgets title bar
	 * @var string
	 */
    public $widgetLabel;

    protected $isCollapsed = false;

    private $_openTag;

    /**
     * @var string The prefix used on the id of the container
     */
    public static $idPrefix = 'x2widget_';


    /**
     * @var string The class of the container
     */
    public static $class = 'sidebar-left';

    /**
     * Class added to the porlet decoration to indicate that the widget is collapsed
     * @var string 
     */
    public static $leftWidgetCollapsedClass = 'left-widget-collapsed';

    public static function registerScript () {
        // collapse or expand left widget and save setting to user profile
        Yii::app()->clientScript->registerScript('leftWidgets','
            $(".left-widget-min-max").click(function(e){
                e.preventDefault();
                var link=this;
                var action = $(this).attr ("value");
                $.ajax({
                    url:"'.Yii::app()->request->getScriptUrl ().'/site/minMaxLeftWidget'.'",
                    data:{
                        action: action,
                        widgetName: $(link).attr ("name")
                    },
                    success:function(data){
                        if (data === "failure") return;
                        if(action === "expand"){
                            $(link).removeClass("fa-caret-left");
                            $(link).addClass("fa-caret-down");
                            $("ggads").html("<img src=\'"+yii.themeBaseUrl+"/images/icons/'.
                                'Collapse_Widget.png\' />");
                            $(link).parents(".portlet-decoration").next().slideDown();
                            $(link).attr ("value", "collapse");
                            $(link).parents (".portlet-decoration").parent ().
                                removeClass ("'.self::$leftWidgetCollapsedClass.'")
                        }else if(action === "collapse"){
                            $(link).removeClass("fa-caret-down");
                            $(link).addClass("fa-caret-left");
                            $("ggads").html("<img src=\'"+yii.themeBaseUrl+"/images/icons/'.
                                'Expand_Widget.png\' />");
                            $(link).parents(".portlet-decoration").next().slideUp();
                            $(link).attr ("value", "expand");
                            $(link).parents (".portlet-decoration").parent ().
                                addClass ("'.self::$leftWidgetCollapsedClass.'")
                        }
                    }
                });
            });
        ');
    }

	/**
	 * Sets the label in the widget title and determines whether this left widget should 
     * be hidden or shown on page load.
	 */
    protected function initTitleBar () {
        $profile = Yii::app()->params->profile;
        if(isset($profile)){
            $layout = $profile->getLayout ();
            if (in_array ($this->widgetName, array_keys ($layout['left']))) {
                $this->isCollapsed = $layout['left'][$this->widgetName]['minimize'];
            }
        }
        $themeURL = Yii::app()->theme->getBaseUrl();
		$this->title =
            Yii::t('app', $this->widgetLabel).
            CHtml::tag( 'i',
                array(
                    'title'=>Yii::t('app', $this->widgetLabel), 
                    'name'=>$this->widgetName, 
                    'class'=>'fa fa-lg right left-widget-min-max '.($this->isCollapsed ? 'fa-caret-left' : 'fa-caret-down'),
                    'value'=>($this->isCollapsed ? 'expand' : 'collapse'),
                    ), ' '
            );
        $this->htmlOptions = array(
            'class' => (!$this->isCollapsed ? "" : "hidden-filter")
        );

    }

	/**
     * overrides parent method so that content gets hidden/shown depending on value
     * of isCollapsed
     *
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
	 */
	public function init()
	{
        if (!$this->widgetName) {
            $this->widgetName = get_called_class();
        }
        /* x2modstart */ 
        $this->initTitleBar ();
        /* x2modend */ 

		ob_start();
		ob_implicit_flush(false);

		if(isset($this->htmlOptions['id']))
			$this->id=$this->htmlOptions['id'];
		else
			$this->htmlOptions['id']=$this->id;

        /* x2modstart */ 
        if ($this->isCollapsed)
            $this->htmlOptions['class'] = self::$leftWidgetCollapsedClass;
        /* x2modend */ 

		echo CHtml::openTag($this->tagName,$this->htmlOptions)."\n";
		$this->renderDecoration();
        /* x2modstart */ 
		echo "<div class=\"{$this->contentCssClass}\" ".
            ($this->isCollapsed ? "style='display: none;'" : '').">\n";
        /* x2modend */ 

		$this->_openTag=ob_get_contents();
		ob_clean();
	}

	/**
	 * Overrides parent method since private property _openTag gets set in init ().
     * This is identical to the parent method.
     *
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
	 */
	public function run()
	{
		$this->renderContent();
		$content=ob_get_clean();
		if($this->hideOnEmpty && trim($content)==='')
			return;
		echo $this->_openTag;
		echo $content;
		echo "</div>\n";
		echo CHtml::closeTag($this->tagName);
	}


    /**
     * Instantiates a left Widget with the specified settings
     * @param array $settings the array of settings to be passed to the widget
     */
    public static function instantiateWidget ($settings=array()) {
        $class = get_called_class();
        echo CHtml::openTag('div', array(
            'id' => self::$idPrefix.$class,
            'class' => self::$class ));

        Yii::app()->controller->widget($class, $settings);

        echo "</div>";
    }
}
?>
