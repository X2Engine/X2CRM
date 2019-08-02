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




class MassRenameFileSysObj extends BaseDocsMassAction {

    public $hasButton = false;

    public $allowMultiple = false;

    /**
     * Renders the mass action dialog, if applicable
     * @param string $gridId id of grid view
     */
    public function renderDialog ($gridId, $modelName) {
        echo "
            <div class='mass-action-dialog' 
             id='".$this->getDialogId ($gridId)."' style='display: none;'>
                <span class='new-name'>".CHtml::encode (Yii::t('docs', 'New name:')).
                "</span>
                <input name='newName' type='text' val='' />
            </div>";
    }

    /**
     * @return string label to display in the dropdown list
     */
    public function getLabel () {
        if (!isset ($this->_label)) {
            $this->_label = Yii::t('app', 'Rename');
        }
        return $this->_label;
    }

    public function getPackages () {
        return array_merge (parent::getPackages (), array (
            'MassRenameFileSysObjJS' => array(
                'baseUrl' => Yii::app()->request->baseUrl,
                'js' => array(
                    'js/X2GridView/MassRenameFileSysObj.js',
                ),
                'depends' => array ('BaseDocsMassActionJS'),
            ),
        ));
    }

    /**
     * @param array $gvSelection array of ids of records to perform mass action on
     */
    public function execute (array $gvSelection) {
        if (Yii::app()->controller->modelClass !== 'Docs' ||
            count ($gvSelection) > 1 ||
            !isset ($_POST['selectedObjs']) || !is_array ($_POST['selectedObjs']) ||
            count ($_POST['selectedObjs']) !== count ($gvSelection) ||
            !isset ($_POST['selectedObjTypes']) || !is_array ($_POST['selectedObjTypes']) ||
            count ($_POST['selectedObjTypes']) !== count ($gvSelection) ||
            !in_array ($_POST['selectedObjTypes'][0], array ('doc', 'folder')) ||
            !isset ($_POST['newName'])) {
            
            throw new CHttpException (400, Yii::t('app', 'Bad Request'));
        }

        $selectedObjId = array_pop ($_POST['selectedObjs']);
        $type = array_pop ($_POST['selectedObjTypes']);
        $newName = $_POST['newName'];

        if ($type === 'doc') {
            $obj = Docs::model ()->findByPk ($selectedObjId);
        } else { // $type === 'folder'
            $obj = DocFolders::model ()->findByPk ($selectedObjId);
        }

        if (!$obj) {
            self::$errorFlashes[] = 
                Yii::t('app', 'Selected {type} does not exist', array (
                    '{type}' => $type === 'doc' ? ucfirst ($type) : $type,
                ));
            return 0;
        }

        if (!Yii::app()->controller->checkPermissions ($obj, 'edit')) {
            self::$errorFlashes[] = 
                Yii::t('app', 'You do not have permission to edit this {type}.', array (
                    '{type}' => $type === 'doc' ? ucfirst ($type) : $type,
                ));
            return 0;
        } 

        if ($type === 'doc' && 
            !Yii::app()->params->isAdmin && 
            !in_array ('name', Docs::model ()->getEditableAttributeNames ())) {

            self::$errorFlashes[] = 
                Yii::t('app', 'You do not have permission to rename Docs.');
            return 0;
        }

        $obj->name = $newName;
        $successes = 0;
        if ($obj->save (true, array ('name'))) {
            self::$successFlashes[] = Yii::t(
                'app', 
                'Renamed {type}', array(
                    '{type}' => $type === 'doc' ? ucfirst ($type) : $type,
                )
            );
            $successes = 1;
        } else {
            self::$errorFlashes[] = Yii::t(
                'app', 
                'Failed to renamed {type}', array(
                    '{type}' => $type === 'doc' ? ucfirst ($type) : $type,
                )
            );
        }
        return $successes;
    }

}

?>
