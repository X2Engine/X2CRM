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




class RecordViewLayoutManager extends X2Widget {

    public $miscLayoutSettingsKey = 'recordViewColumnWidth';

    public $namespace = 'recordView'; 

    public $mainColumnSelector = '#main-column';

    /**
     * @var bool $staticLayout
     */
    public $staticLayout = true; 

    public $JSClass = 'RecordViewLayoutEditor'; 

    private static $editLayoutButtonId = 'edit-record-view-layout';

    public $columnWidthPercentage = 65;

    private $responsiveCssId = 'main-column-responsive-css';

    public static function getEditLayoutActionMenuListItem () {
        return array(
            'name'=>'editLayout',
            'linkOptions' => array (
                'id' => self::$editLayoutButtonId,
            ),
            'label'=>Yii::t('app', 'Edit Layout'),
            'url'=>array('#')
        );
    }

    public static function getViewActionMenuListItem ($modelId) {
        if (Yii::app()->controller->action->getId () === 'view') {
            return array(
                'name'=>'view',
                'label' => 
                    Yii::t('app', 'View').X2Html::minimizeButton (array (
                        'class' => 'record-view-type-menu-toggle',
                    ), '#record-view-type-menu', true, 
                    Yii::app()->params->profile->miscLayoutSettings['viewModeActionSubmenuOpen']), 
                'encodeLabel' => false,
                'url' => array('view', 'id' => $modelId),
                'linkOptions' => array (
                    // click minimize button
                    'onClick' => '$(this).find ("i:visible").click ();', 
                ),
                'itemOptions' => array (
                    'id' => 'view-record-action-menu-item',
                ),
                'submenuOptions' => array (
                    'id' => 'record-view-type-menu',
                    'style' => 
                        Yii::app()->params->profile->miscLayoutSettings
                            ['viewModeActionSubmenuOpen'] ? '' : 'display: none;',
                ),
                'items' => array (
                    array (
                        'encodeLabel' => false,
                        'name'=>'journalView',
                        'label' => CHtml::checkBox (
                            'journalView', 
                            Yii::app()->params->profile->miscLayoutSettings
                                ['enableJournalView'], 
                            array (
                                'class' => 'journal-view-checkbox',
                            )).CHtml::label (Yii::t('app', 'Journal View'), 'journalView'),
                    ),
                    array (
                        'encodeLabel' => false,
                        'name'=>'transactionalView',
                        'label' => CHtml::checkBox (
                            'transactionalView', 
                            Yii::app()->params->profile->miscLayoutSettings[
                                'enableTransactionalView'], 
                            array (
                                'class' => 'transactional-view-checkbox',
                            )).CHtml::label (
                                Yii::t('app', 'List View'), 'transactionalView'),
                    ),
                ),
            );
        } else {
            return array(
                'name'=>'view',
                'label' => Yii::t('app', 'View'),
                'encodeLabel' => true,
                'url' => array('view', 'id' => $modelId),
            );
        }
    }

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $baseUrl = Yii::app()->getBaseUrl ();
            $this->_packages = array_merge (parent::getPackages (), array (
                'layoutEditorJS' => array(
                    'baseUrl' => $baseUrl.'/js/',
                    'js' => array(
                        'LayoutEditor.js',
                        'RecordViewLayoutEditor.js',
                    ),
                    'depends' => array ('auxlib'),
                ),
                'layoutEditorCss' => array(
                    'baseUrl' => Yii::app()->theme->getBaseUrl (),
                    'css' => array(
                        '/css/components/views/layoutEditor.css',
                    )
                ),
            ));
        }
        return $this->_packages;
    }

    public function getJSClassParams () {
        if (!isset ($this->_JSClassParams)) {
            $this->_JSClassParams = array_merge (parent::getJSClassParams (), array (
                'defaultWidth' => '65',
                'columnWidth' => $this->columnWidthPercentage,
                'editLayoutButton' => '#'.self::$editLayoutButtonId,
                'container' => '#'.$this->namespace.'-layout-editor',
                'draggable' => '#'.$this->namespace.'-section-1',
                'mainColumnSelector' => $this->mainColumnSelector,
                'responsiveCssSelector' => '#'.$this->responsiveCssId,
                'minWidths' => array (50, 35),
                'column1' => array (
                    '#RecordViewWidgetManagerwidgets-container-2',
                    '#main-column',
                    '#'.$this->namespace.'-section-1',
                ),
                'column2' => array (
                    '#RecordViewWidgetManagerwidgets-container',
                ),
        	    'miscSettingsUrl' => 
                    Yii::app()->controller->createUrl('/profile/saveMiscLayoutSetting'),
                'settingName' => $this->miscLayoutSettingsKey,
                'dimensions' => array (
                    'singleColumnThresholdNoWidgets' => $this->singleColumnThresholdNoWidgets, 
                    'singleColumnThreshold' => $this->singleColumnThreshold, 
                    'extraContentWidth' => $this->extraContentWidth, 
                    'rightWidgetWidth' => $this->rightWidgetWidth, 
                    'formLayoutWidthThreshold' => $this->formLayoutWidthThreshold ,
                ),
            ));
        }
        return $this->_JSClassParams;
    }

	public function init() {
		$miscLayoutSettings = Yii::app()->params->profile->miscLayoutSettings;
		if (!$this->staticLayout &&
            isset($miscLayoutSettings[$this->miscLayoutSettingsKey])) {

			$this->columnWidthPercentage = $miscLayoutSettings[$this->miscLayoutSettingsKey];
		}
        if (!$this->staticLayout) {
            $this->registerPackages ();
            $this->instantiateJSClass ();
        }

		parent::init ();
	}

    public function columnWidthStyleAttr ($columnNumber) {
        $columnWidth = $this->getColumnWidth (1);
        return "style='width: {$columnWidth};'";
    }

    public function getColumnWidth ($columnNumber) {
        $columnWidths = $this->getColumnWidths ($columnNumber); 
        return $columnWidths[$columnNumber - 1];
    }

    public function responsiveCss ($parentSelector) {
        return "
            $parentSelector .formSectionRow td {
                // display: block;
                // width: 100% !important;
            }
            $parentSelector .formSectionRow td > div{
                // width: 100% !important;
            }
            $parentSelector .formItem.leftLabel > .formInputBox {
                // width: 200px !important;
            }";
    }

    private $singleColumnThresholdNoWidgets = 1130; 
    private $singleColumnThreshold = 1407; 
    private $extraContentWidth = 160; 
    private $rightWidgetWidth = 280; 
    private $formLayoutWidthThreshold = 630; 

    public function registerResponsiveCss () {
        
        $columnWidthPercentage = $this->columnWidthPercentage / 100;

        // dynamic media queries which get removed if user edits layout
        Yii::app()->clientScript->registerCss('RecordViewLayoutManager::registerResponsiveCss',
            array (
            'text' => "
            @media (min-width:{$this->singleColumnThreshold}px) and (max-width: {$this->getFormLayoutResponsiveThreshold (true)}px) {
                {$this->responsiveCss ('body.show-widgets')}
            }
            @media (min-width:{$this->singleColumnThresholdNoWidgets}px) and (max-width: {$this->getFormLayoutResponsiveThreshold (false)}px) {
                {$this->responsiveCss ('body.no-widgets')}
            }",
            'htmlOptions' => array (
                'id' => $this->responsiveCssId,
            ),
        ));
        // class-based responsive css which gets applied after user edits layout until page refresh
        Yii::app()->clientScript->registerCss('RecordViewLayoutManager::registerResponsiveCss2',
            $this->responsiveCss ($this->mainColumnSelector . '.force-single-column'));
    }

    public function run () {
        $layoutEditor = $this->render ('layoutEditor', array (
            'namespace' => $this->namespace,
        ), true);
        $this->registerResponsiveCss ();
        Yii::app()->clientScript->registerScript('RecordViewLayoutManager::run',"
            $('#main-column').before (".CJSON::encode ($layoutEditor).");
        ", CClientScript::POS_END);
    }

    private $_columnWidths;
    public function getColumnWidths () {
        if ($this->staticLayout) return array ('100%', '100%'); 
        if (!isset ($this->_columnWidths)) {
            if(!$this->columnWidthPercentage) {
                $this->_columnWidths = array('', '');
            } else {
                $column1 = $this->columnWidthPercentage;
                $column2 = 100 - $column1;

                $column1 = $column1.'%';
                $column2 = $column2.'%';

                $this->_columnWidths = array(
                    $column1,
                    $column2
                );
            }
        }
        return $this->_columnWidths;
    }

    private function getFormLayoutResponsiveThreshold ($rightWidgets=true) {
        $columnWidthRatio = $this->columnWidthPercentage / 100;
        $extraContentWidth = $this->extraContentWidth;
        if ($rightWidgets) {
            $extraContentWidth += $this->rightWidgetWidth;
        }
        return $extraContentWidth + $this->formLayoutWidthThreshold + 
            ($this->formLayoutWidthThreshold * ( 1 - $columnWidthRatio)) / $columnWidthRatio;
    }

}

?>
