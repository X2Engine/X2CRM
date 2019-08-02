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
 * Model to provide one-time tips to help user learn a feature.
 * @package application.models
 * @author Alex Rowe <alex@x2engine>
 */
class Tours extends CActiveRecord {

    /**
     * Whethers tours JS has been registered or not
     * @var boolean
     */
    private static $registerJS = true;

    /**
     * Default params for the config array 
     * @see Tours::tip()
     */
    private static $defaultParams = array (

        // if set to true, will render a partial in the tour body
        // ex. Tours::tip('application.views._myTipPartial', array('partial' => true));
        'partial' => false,

        // View File params to be passed to the view file. Only relevant
        // if 'partial' is present
        'viewParams' => array(),

        // Type of tip to determine tip class
        'type' => 'flash',

        // If set to a string, content will be translated before being rendered.
        'translate' => false,

        // Extra Html options to be merged in 
        'htmlOptions' => array(),


        // Array of string replacements for easy interpolation
        'replace' => array(),

        // Array of string replacements for easy interpolation
        'title' => '',

        // Optional key for the tip. Default is a md5 hash of the content
        'key' => null,

        // To revise a tip, put the new content in the revision key to retain the 
        // Tip being seen on installations. 
        'revision' => '',

        // --- PopUp Type Specific Keys ---
        
        // Wether or not to draw a border around the target element
        'highlight' => false,

        // Target element to attach the tip to. Fed to JS as a selector.
        // ex. #edit-profile-button
        'target' => null,
    );

	/**
	 * @return string the associated database table name
	 */
	public function tableName () {
		return 'x2_tours';
	}

    public static function model ($className='Tours'){
        return parent::model($className);
    }

    /**
     * Main API to create a tip. 
     *
     * EXAMPLES:
     *---------------------------------------------------
     * 1.This will display as a flash-style tip with the specified text
     *     Tours::tip (array(
     *         'content' => "<h1>Welcome to the profile page</h1>"
     *     ));
     *     
     *---------------------------------------------------
     * 2. This example will create a Q-tip dialog,
     * and highlight the element '#create-new-user-button'.
     *     Tours::tip ( array(
     *         'content' => 'The button here will create a new user", 
     *         'target' => '#create-new-user-button'
     *     ));
     *     
     * 
     * @param  string $content HTML, text, or partial alias OR array of tips
     * @param  array  $params  Config array (Described below)
     * @return string          HTML for a tip
     */
    public static function tip ($params=array(), $return = false) {
        // $params can be a string for an simple tip
        if (is_string($params)) {
            return self::tip (array('content' => $params), $return);
        }

        // If content is not set, no tip can be created
        if (!isset($params['content'])) {
            throw new Exception("Tips must include a 'content' key");
        }
        
        // Dont show tips if user has tips off
        if (!Yii::app()->params->profile->showTours) {
            return;
        }

        // Register JS if it is the first tip rendered
        if (Tours::$registerJS) {
            Yii::app()->clientScript->registerPackage('tours');
            Tours::$registerJS = false;
        }

        // Merge Paramters with default
        $params = array_merge (self::$defaultParams, $params);
        
        $content = $params['content'];

        // By default the key is an md5 of the content
        if ($params['key']) {
            $key = $params['key'];
        } else {
            $key = md5($content);
        }

        // Get a tip if it hasn't been seen
        $tour = self::getTip ($key);
        if(!$tour) {
            return;
        }  

        if ($params['revision']) {
            $content = $params['revision'];
        }

        // Merge Html Options, Default class string
        $htmlOptions = array_merge (array(
            'class' => '',
        ), $params['htmlOptions']);


        // Translate if specified
        if($params['translate']) {
            $content = Yii::t($params['translate'], $content);
        }

        // set content to partial if specified
        if ($params['partial']) {
            $content = Yii::app()->controller->renderPartial (
                $content, $params['viewParams'], true);
        }

        // Replace all replacements
        foreach ($params['replace'] as $key => $value) {
            $content = preg_replace("/$key/", $value, $content);
        }

        // --- Popup specifics -- 
        // If target is set, create a popup classed tour
        if ($params['target']) {
            $params['type'] = 'popup';
            $htmlOptions['data-target'] = $params['target'];
        }

        // set content to partial if specified
        if ($params['highlight']) {
            $htmlOptions['data-highlight'] = true;
        }

        // Set the type of tip as a class
        $htmlOptions['class'] .= " $params[type]";

        // Return rendered partial
        $html = self::render($tour, $content, $params, $htmlOptions);

        if ($return) {
            return $html;
        } else {
            echo $html;
        }
    }

    /**
     * renders an array of tips
     * @param  array   $tips   array of tip arrays. @see self::tip
     * @param  boolean $return Wether or not to return html
     * @return string          empty if return = false
     */
    public static function tips ($tips = array(), $return = false){
        $html = '';

        foreach ($tips as $tip) {
            $html .= self::tip ($tip, $return);
        }

        return $html;
    }

    /**
     * Renders a file specifically to view tips. 
     * It is a simple rener partial call and the partial should echo out the tips
     * @param  string  $partial Partial name (in components/tours)
     * @param  boolean $return  wether to return or echo the contents
     * @return string           contents if return is true
     */
    public static function loadTips($partial=null, $return=false) {
        if (!$partial) {
            $partial = Yii::app()->controller->id.'.'.Yii::app()->controller->action->id;
        }

        return Yii::app()->controller->renderPartial (
            "application.views.tours.$partial", $return);
    }

    /**
     * Checks if a tip has been seen returns false if it has been seen
     * @param  string $key Key to find a tip for
     * @return mixed  Returns false if the tip has been seen, and returns the tip object
     * if it has not been seen yet.
     */
    public static function getTip ($key) {
        $tip = self::model('Tours')->findByAttributes (array(
            'profileId' => Yii::app()->params->profile->id,
            'description' => $key,
        ));


        if ($tip && $tip->seen) {
            return false;
        }

        if (empty($tip)) {
            $tip = new Tours;
            $tip->profileId = Yii::app()->params->profile->id;
            $tip->description = $key;
            $tip->save();
        }

        return $tip;
    }

    private static function render($tour, $content, $params, $htmlOptions = array()) {
        return Yii::app()->controller->renderPartial ('application.views.tours.tour', array(
            'tour' => $tour,
            'title' => $params['title'],
            'content' => $content, 
            'htmlOptions' => $htmlOptions
        ), true);
    }


}
