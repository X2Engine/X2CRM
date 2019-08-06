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



Yii::import('zii.widgets.CListView');

/**
 * Renders a CListView
 *
 * @package application.components
 */
class X2ListView extends CListView {

    protected $ajax = false;
    private $afterGridViewUpdateJSString = "";
    private $beforeGridViewUpdateJSString = "";

	public function __construct ($owner=null) {
        parent::__construct ($owner);
        $this->attachBehaviors ($this->behaviors ());
	}

    public function behaviors () {
        return array (
            'BaseListViewBehavior' => 'application.components.behaviors.BaseListViewBehavior'
        );
    }

    public function init(){
        if ($this->pager === array('class'=>'CLinkPager')) {
            $this->pager = array (
                'header' => '',
                'firstPageCssClass' => '',
                'lastPageCssClass' => '',
                'prevPageLabel' => '<',
                'nextPageLabel' => '>',
                'firstPageLabel' => '<<',
                'lastPageLabel' => '>>',
            );
        }

        $this->asa ('BaseListViewBehavior')->setSummaryText ();

        $this->ajax = isset($_GET['ajax']) && $_GET['ajax'] === $this->id;

        if($this->ajax && ob_get_length ()) {
            ob_clean();
        }

        if($this->itemView === null)
            throw new CException(Yii::t('app', 'The property "itemView" cannot be empty.'));
        parent::init();

        if(!isset($this->htmlOptions['class']))
            $this->htmlOptions['class'] = 'list-view';

        if($this->baseScriptUrl === null)
            $this->baseScriptUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('zii.widgets.assets')).'/listview';

        if($this->cssFile !== false){
            if($this->cssFile === null)
                $this->cssFile = $this->baseScriptUrl.'/styles.css';
            Yii::app()->getClientScript()->registerCssFile($this->cssFile);
        }
    }

    public function run(){
        $this->registerClientScript();

        echo CHtml::openTag($this->tagName, $this->htmlOptions)."\n";

        $this->renderContent();
        $this->renderKeys();
        if($this->ajax){
            // remove any external JS and CSS files
            Yii::app()->clientScript->scriptMap['*.css'] = false;
            // remove JS for gridview checkboxes and delete buttons (these events use jQuery.on() and shouldn't be reapplied)
            Yii::app()->clientScript->registerScript('CButtonColumn#C_gvControls', null);
            Yii::app()->clientScript->registerScript('CCheckBoxColumn#C_gvCheckbox', null);

            $output = '';
            Yii::app()->getClientScript()->renderBodyEnd($output);
            echo $output;

            echo CHtml::closeTag($this->tagName);
            ob_flush();


            Yii::app()->end();
        }
        echo CHtml::closeTag($this->tagName);
    }

    public function addToAfterAjaxUpdate ($str) {
        $this->afterGridViewUpdateJSString .= $str;
        if ($this->ajax) return;
        $this->afterAjaxUpdate =
            'js: function(id, data) {'.
                $this->afterGridViewUpdateJSString.
            '}';
    }

    public function addToBeforeAjaxUpdate ($str) {
        $this->beforeGridViewUpdateJSString .= $str;
        if ($this->ajax) return;
        $this->beforeAjaxUpdate =
            'js: function(id, data) {'.
                $this->beforeGridViewUpdateJSString .
            '}';
    }
}

?>
