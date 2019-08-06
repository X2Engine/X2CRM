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
 * Widget class for the relationships form.
 *
 * Relationships lists the relationships a model has with other models,
 * and provides a way to add existing models to the models relationships.
 *
 * @package application.components.sortableWidget
 */
class InlineRelationshipsWidget extends GridViewWidget {

     

    public $viewFile = '_inlineRelationshipsWidget';

	public $model;

    public $template = '<div class="submenu-title-bar widget-title-bar">{widgetLabel}{titleBarButtons}{closeButton}{minimizeButton}{settingsMenu}</div>{widgetContents}';

    /**
     * Used to prepopulate create relationship forms
     * @var array (<model class> => <array of default values indexed by attr name>)
     */
    public $defaultsByRelatedModelType = array ();

    protected $compactResultsPerPage = true; 

	private $_relatedModels;

    private static $_JSONPropertiesStructure;

    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'label' => 'Relationships',
                    'hidden' => false,
                    'resultsPerPage' => 10, 
                    'showHeader' => false,
                    'displayMode' => 'grid', // grid | graph
                    'height' => '200',
                    'hideFullHeader' => true, 
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    private $_filterModel;
    public function getFilterModel () {
        if (!isset ($this->_filterModel)) {
            $model = $this->model;
            $filterModel = new RelationshipsGridModel ('search');
            $filterModel->myModel = $model;
            $filterModel->init ();
            $this->_filterModel = $filterModel;
        }
        return $this->_filterModel;
    }

    public function getDataProvider () {
        $model = $this->model;
        $filterModel = $this->getFilterModel ();

        // convert related models into grid models
        $gridModels = array ();
        foreach ($model->visibleRelatedX2Models as $relatedModel) {
            $gridModel = Yii::createComponent (array (
                'class' => 'RelationshipsGridModel', 
                'relatedModel' => $relatedModel,
                'myModel' => $model,
            ));
            $gridModel->init ();
            $gridModels[] = $gridModel;
        }

        // use filter model to filter grid models based on GET params
        $gridModels = $filterModel->filterModels ($gridModels);
        //$gridModels = $filterModel->sortModels ($gridModels, $this->widgetKey.'_sort');

        $sort = Yii::createComponent (array (
            'class' => 'SmartSort',
            'uniqueId' => $this->widgetKey,
            'sortVar' => $this->widgetKey.'_sort',
            'attributes'=>array('name','relatedModelName','label','createDate','assignedTo'),
            'defaultOrder' => 'id desc',
            'alwaysApplyDefaultOrder' => true
        ));

        $relationshipsDataProvider = new CArrayDataProvider($gridModels, array(
            'id' => 'relationships-gridview',
            'sort' => $sort,
            'pagination' => array('pageSize'=>$this->getWidgetProperty ('resultsPerPage'))
        ));
        return $relationshipsDataProvider;
    }

    public function renderTitleBarButtons () {
        echo '<div class="x2-button-group">';
        if ($this->checkModuleUpdatePermissions ()) {
            echo 
                "<a class='x2-button rel-title-bar-button' id='new-relationship-button' 
                  title='".CHtml::encode (Yii::t('app', 'Create a new relationship'))."'>".
                    X2Html::fa ('fa-plus', array (), ' ', 'span').
                "</a>";
        }
         
        $displayMode = $this->getWidgetProperty ('displayMode');
        $lastChild = Yii::app()->params->isAdmin ? '' : 'x2-last-child';
        echo 
            "<a class='x2-button rel-title-bar-button $lastChild' id='inline-graph-view-button'
              title='".Yii::t('app', 'Inline Graph')."'
              style='".($displayMode === 'graph' ? 'display: none;' : '')."'>".
                X2Html::fa ('fa-share-alt', array (
                )).
            "</a>";
        echo 
            "<a class='x2-button rel-title-bar-button $lastChild' id='rel-grid-view-button'
              title='".Yii::t('app', 'Grid')."'
              style='".($displayMode === 'grid' ? 'display: none;' : '')."'>".
                X2Html::fa ('fa-th-list', array (
                )).
            "</a>";
        if (Yii::app()->params->isAdmin) {
            echo "<a href='".Yii::app()->createUrl ('/relationships/graph', array (
                'recordType' => get_class ($this->model),
                'recordId' => $this->model->id,
            ))."' class='x2-button rel-title-bar-button' title='".Yii::t('app', 'Full Graph')."'>".
                X2Html::fa ('fa-share-alt-square', array (
            )).
            "</a>";
        }
         
        echo '</div>';
    }

    public function renderWidgetLabel () {
        $label = $this->getWidgetLabel ();
        $relationshipCount = count ($this->model->getVisibleRelatedX2Models ());
        echo "<div class='widget-title'>".
            htmlspecialchars($label).
            "&nbsp(<span id='relationship-count'>$relationshipCount</span>)</div>";
    }

    public function getSetupScript () {
        if (!isset ($this->_setupScript)) {
            $widgetClass = get_called_class ();
            if (isset ($_GET['ajax'])) {
                $this->_setupScript = "";
            } else {
                $modelsWhichSupportQuickCreate = 
                    QuickCreateRelationshipBehavior::getModelsWhichSupportQuickCreate ();

                // get create action urls for each linkable model
                $createUrls = QuickCreateRelationshipBehavior::getCreateUrlsForModels (
                    $modelsWhichSupportQuickCreate);

                // get create relationship tooltips for each linkable model
                $tooltips = QuickCreateRelationshipBehavior::getDialogTooltipsForModels (
                    $modelsWhichSupportQuickCreate, get_class ($this->model));

                // get create relationship dialog titles for each linkable model
                $dialogTitles = QuickCreateRelationshipBehavior::getDialogTitlesForModels (
                    $modelsWhichSupportQuickCreate);
                $this->_setupScript = "
                    $(function () {
                        x2.inlineRelationshipsWidget = new x2.InlineRelationshipsWidget (".
                            CJSON::encode (array_merge ($this->getJSSortableWidgetParams (), array (
                                'displayMode' => $this->getWidgetProperty ('displayMode'),
                                'widgetClass' => $widgetClass,
                                'setPropertyUrl' => Yii::app()->controller->createUrl (
                                    '/profile/setWidgetSetting'),
                                'cssSelectorPrefix' => $this->widgetType,
                                'widgetType' => $this->widgetType,
                                'widgetUID' => $this->widgetUID,
                                'enableResizing' => true,
                                'height' => $this->getWidgetProperty ('height'),
                                'recordId' => $this->model->id,
                                'recordType' => get_class ($this->model),
                                'defaultsByRelatedModelType' => 
                                    $this->defaultsByRelatedModelType,
                                'createUrls' => $createUrls,
                                'dialogTitles' => $dialogTitles,
                                'tooltips' => $tooltips,
                                'modelsWhichSupportQuickCreate' => 
                                    array_values ($modelsWhichSupportQuickCreate),
                                'ajaxGetModelAutocompleteUrl' => 
                                    Yii::app()->controller->createUrl ('ajaxGetModelAutocomplete'),
                                'createRelationshipUrl' => 
                                    Yii::app()->controller->createUrl (
                                        '/relationships/addRelationship'),
                                'hasUpdatePermissions' => $this->checkModuleUpdatePermissions (),
                            )))."
                        );
                    });
                ";
            }
        }
        return $this->_setupScript;
    }

    /**
     * overrides parent method. Adds JS file necessary to run the setup script.
     */
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (
                parent::getPackages (),
                array (
                    'InlineRelationshipsJSExt' => array(
                        'baseUrl' => Yii::app()->getTheme ()->getBaseUrl ().'/css/gridview/',
                        'js' => array (
                            'jquery.yiigridview.js',
                        ),
                        'depends' => array ('auxlib')
                    ),
                    'InlineRelationshipsJS' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array (
                            'js/sortableWidgets/InlineRelationshipsWidget.js',
                        ),
                        'depends' => array ('SortableWidgetJS')
                    ),
                )
            );
        }
        return $this->_packages;
    }

    public function getViewFileParams () {
        if (!isset ($this->_viewFileParams)) {
            $linkableModels = Relationships::getRelationshipTypeOptions ();
            asort ($linkableModels);
             
            if(!Yii::app()->user->checkAccess('MarketingAdminAccess')) {
                unset ($linkableModels['AnonContact']);
            }
             

            // used to instantiate html dropdown
            $linkableModelsOptions = $linkableModels;

            $hasUpdatePermissions = $this->checkModuleUpdatePermissions ();

            $this->_viewFileParams = array_merge (
                parent::getViewFileParams (),
                array (
                    'model' => $this->model,
                    'modelName' => get_class ($this->model),
                    'linkableModelsOptions' => $linkableModelsOptions,
                    'hasUpdatePermissions' => $hasUpdatePermissions,
                     
                    'displayMode' => $this->getWidgetProperty ('displayMode'),
                     
                    'height' => $this->getWidgetProperty ('height'),
                )
            );
        }
        return $this->_viewFileParams;
    } 

    protected function getSettingsMenuContentEntries () {
        return 
            '<li class="expand-detail-views">'.
                X2Html::fa('fa-toggle-down').
                Yii::t('profile', 'Toggle Detail Views').
            '</li>'.
            parent::getSettingsMenuContentEntries ();
    }


    private $_moduleUpdatePermissions;
    private function checkModuleUpdatePermissions () {
        if (!isset ($this->_moduleUpdatePermissions)) {
            $this->_moduleUpdatePermissions = 
                Yii::app()->controller->checkPermissions ($this->model, 'edit');
        }
        return $this->_moduleUpdatePermissions;
    }

    public function init ($skipGridViewInit=false) {
        return parent::init (true);
    }
}

?>
