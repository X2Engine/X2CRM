<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

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
