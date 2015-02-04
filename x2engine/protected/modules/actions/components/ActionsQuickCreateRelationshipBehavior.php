<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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

class ActionsQuickCreateRelationshipBehavior extends QuickCreateRelationshipBehavior {

    protected $inlineFormPathAlias = 'application.modules.actions.views.actions._form'; 

    /**
     * Renders an inline record create/update form
     * @param object $model 
     * @param bool $hasErrors
     */
    public function renderInlineForm ($model, $hasErrors, array $viewParams = array ()) {
        $formModel = new ActionsQuickCreateFormModel;
        $formModel->attributes = $_POST;
        $formModel->validate ();
        $actionType = $formModel->actionType;
        if ($actionType) {
            $secondModelName = $formModel->secondModelName;
            $secondModelId = $formModel->secondModelId;
            $associationType = X2Model::getAssociationType ($secondModelName);
            $model->associationType = X2Model::getAssociationType ($secondModelName);

            $tabClass = Publisher::$actionTypeToTab[$actionType];
            $tab = new $tabClass;
            $tab->namespace = get_class ($this);
            $tab->startVisible = true;

            $this->owner->widget('Publisher', array(
                'associationType' => $associationType,
                'associationId' => $model->id,
                'assignedTo' => Yii::app()->user->getName(),
                'calendar' => false,
                'renderTabs' => false,
                'tabs' => array ($tab),
                'namespace' => $tab->namespace,
            ));
        }

        switch ($actionType) {
            case 'action':
            case 'call':
            case 'note':
            case 'event':
            case 'time':
            case 'products':
                $this->owner->renderPartial (
                    'application.components.views.publisher.tabFormContainer', 
                    array (
                        'tab' => $tab,
                        'model' => $formModel->getAction (),
                        'associationType' => $model->associationType,
                    ), false, true);
                break;
            default:
                parent::renderInlineForm ($model, $hasErrors, array_merge (array (
                    'namespace' => get_class ($this),
                ), $viewParams));
                //Yii::app()->controller->badRequest (Yii::t('app', 'Invalid action type'));
        }
    }

    public function quickCreate ($model) {
        if (isset ($_POST['SelectedTab'])) {
            $this->owner->actionPublisherCreate ();
        } else {
            return parent::quickCreate ($model);
        }
    }

}

class ActionsQuickCreateFormModel extends X2FormModel {
    public $secondModelName;
    public $secondModelId;
    public $actionType;
    protected $throwsExceptions = true;

    private $_model;
    public function getModel () {
        if (!isset ($this->_model)) {
            $modelName = $this->secondModelName;
            $this->_model = $modelName::model ()->findByPk ($this->secondModelId);
        }
        return $this->_model;
    }

    private $_action;
    public function getAction () {
        if (!isset ($this->_action)) {
            $action = new Actions;
            $action->setAttributes (array (
                'associationType' => X2Model::getAssociationType ($this->secondModelName),
                'associationId' => $this->secondModelId,
                'assignedTo' => Yii::app()->user->getName (),
            ), true);
            $this->_action = $action;
        }
        return $this->_action;
    }

    public function rules () {
        return array (
            array (
                'secondModelName, secondModelId', 'required'
            ),
            array (
                'actionType', 'safe',
            ),
        );
    }
}

?>
