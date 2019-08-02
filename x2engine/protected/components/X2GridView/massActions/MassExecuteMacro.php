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
 * 
 */
class MassExecuteMacro extends MassAction {
    
    private $_macros;
    
    public function getLabel() {
        if (!isset ($this->_label)) {
            $this->_label = Yii::t('app', 'Execute macro');
        }
        return $this->_label;
    }
    
    public function renderDialog ($gridId, $modelName) {
        list($macros, $descriptions) = $this->getMacros($modelName);
        echo "
            <div class='mass-action-dialog' id='".$this->getDialogId ($gridId)."' 
             style='display: none;'>
                <div class='form'>
                    <span class=''>".CHtml::encode(Yii::t('app','Select a macro from the list:'))."</span>
                    ".CHtml::dropDownList('macro' , '', $macros, array('empty'=>Yii::t('app','Select a macro'), 'id'=>'mass-action-macro-selection'))."
                </div>
                <div id='mass-action-macro-description' class='form'></div>
            </div>
            <script>
            x2.MassExecuteMacro.macroDescriptions = ".CJSON::encode(array(''=>'')+$descriptions).";
            </script>";
    }
    
    public function execute(array $gvSelection) {
        $macroId = $_POST['macro'];
        $modelType = $_POST['modelType'];
        if(empty($macroId)){
            throw new CHttpException(400, Yii::t('app','Bad request.'));
        }
        
        $flow = X2Flow::model()->findByPk($macroId);
        if (!isset($flow) || $flow->triggerType !== 'MacroTrigger' || $flow->modelClass !== $modelType) {
            throw new CHttpException(400, 'Invalid flow selected.');
        }
        $params = array('modelClass' => $modelType);
        $macrosExecuted = 0;
        foreach ($gvSelection as $recordId){
            $model = X2Model::model($modelType)->findByPk($recordId);
            if(isset($model)){
                if(Yii::app()->controller->X2PermissionsBehavior->checkPermissions($model, 'view')){
                    $params['model'] = $model;
                    X2Flow::executeFlow($flow, $params, null);
                    $macrosExecuted++;
                }else{
                    $this->addNoticeFlash($recordId);
                }
            }
        }
        $this->addSuccessFlash($macrosExecuted);
    }
    
    public function getPackages () {
        return array_merge (parent::getPackages (), array (
            'MassExecuteMacro' => array(
                'baseUrl' => Yii::app()->request->baseUrl,
                'js' => array(
                    'js/X2GridView/MassExecuteMacro.js',
                ),
                'depends' => array ('X2MassAction'),
            ),
        ));
    }
    
    private function addSuccessFlash($macrosExecuted) {
        self::$successFlashes[] = Yii::t(
                        'app', 'Macro executed on {n} record.|Macro executed on {n} records.',
                        array($macrosExecuted)
        );
    }

    private function addNoticeFlash ($recordId) {
        self::$noticeFlashes[] = Yii::t(
            'app', 'Unable to execute macro on record {recordId}. You may not '.
                'have permission to view this record.', 
            array (
                '{recordId}' => $recordId
            )
        );
    }
    
    private function getMacros($modelType) {
        if(isset($this->_macros)){
            return $this->_macros;
        }
        $macros = array();
        $descriptions = array();

        $flows = Yii::app()->db->createCommand()
                ->select('id, name, description')
                ->from('x2_flows')
                ->where('triggerType = :type AND modelClass = :model',array(':type'=>'MacroTrigger', ':model'=>$modelType))
                ->queryAll();
        foreach($flows as $row){
            $macros[$row['id']] = $row['name'];
            $descriptions[$row['id']] = $row['description'];
        }
        
        $ret = array($macros, $descriptions);
        $this->_macros = $ret;
        return $ret;
    }
    
}
