<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

Yii::import('zii.widgets.CPortlet');


/**
 * Gives a utility function to derived classes which sets up this left widgets title bar.
 * @package X2CRM.components 
 */
abstract class LeftWidget extends CPortlet {

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

	/**
	 * Sets the label in the widget title and determines whether this left widget should 
     * be hidden or shown on page load.
	 */
    protected function initTitleBar () {
        $profile = Yii::app()->params->profile;
        $isCollapsed = false;
        if(isset($profile)){
            $layout = $profile->getLayout ();
            if (in_array ($this->widgetName, array_keys ($layout['left']))) {
                $isCollapsed = $layout['left'][$this->widgetName]['minimize'];
            }
        }
        $themeURL = Yii::app()->theme->getBaseUrl();
		$this->title =
            Yii::t('app', $this->widgetLabel).
            CHtml::link(
                CHtml::image(
                    $themeURL."/images/icons/".(!$isCollapsed?"Collapse":"Expand")."_Widget.png"),
                "#", array(
                    'title'=>Yii::t('app', $this->widgetLabel), 
                    'name'=>$this->widgetName, 
                    'class'=>'left-widget-min-max',
                    'value'=>($isCollapsed ? 'expand' : 'collapse'),
                    'style'=>'float:right;padding-right:5px;')
            );
        $this->htmlOptions = array(
            'class' => (!$isCollapsed ? "" : "hidden-filter")
        );
    }
}
?>
