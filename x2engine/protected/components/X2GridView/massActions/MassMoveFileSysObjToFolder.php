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




class MassMoveFileSysObjToFolder extends BaseDocsMassAction {

    public $hasButton = true;

    /**
     * Renders the mass action dialog, if applicable
     * @param string $gridId id of grid view
     */
    public function renderDialog ($gridId, $modelName) {
        echo "
            <div class='mass-action-dialog' 
             id='".$this->getDialogId ($gridId)."' style='display: none;'>
                <span>".
                    Yii::t('app', 'Move to')."
                </span>
                <span style='display: none;'>".
                    Yii::t('app', 'Move to')."
                </span>
                <span class='target-folder'>".CHtml::encode (Yii::t('docs', 'Docs'))."</span>
                <input name='targetFolderId' type='hidden' val='' />
                <br/>
                <div class='folder-selector'>
                </div>
            </div>";
    }

    /**
     * Renders the mass action button, if applicable
     */
    public function renderButton () {
        if (!$this->hasButton) return;
        
        echo "
            <a href='#' title='".CHtml::encode ($this->getLabel ())."'
             data-mass-action='".get_class ($this)."'
             data-allow-multiple='".($this->allowMultiple ? 'true' : 'false')."'
             class='fa fa-folder fa-lg mass-action-button x2-button mass-action-button-".
                get_class ($this)."'>
            </a>";
    }


    /**
     * @return string label to display in the dropdown list
     */
    public function getLabel () {
        if (!isset ($this->_label)) {
            $this->_label = Yii::t('app', 'Move to');
        }
        return $this->_label;
    }

    public function getPackages () {
        return array_merge (parent::getPackages (), array (
            'X2MassMoveFileSysObjToFolder' => array(
                'baseUrl' => Yii::app()->request->baseUrl,
                'js' => array(
                    'js/X2GridView/MassMoveFileSysObjToFolder.js',
                ),
                'depends' => array ('X2MassAction', 'BaseDocsMassActionJS'),
            ),
        ));
    }

    /**
     * @param array $gvSelection array of ids of records to perform mass action on
     */
    public function execute (array $gvSelection) {
        if (Yii::app()->controller->modelClass !== 'Docs' ||
            !isset ($_POST['selectedObjs']) || !is_array ($_POST['selectedObjs']) ||
            count ($_POST['selectedObjs']) !== count ($gvSelection) ||
            !isset ($_POST['selectedObjTypes']) || !is_array ($_POST['selectedObjTypes']) ||
            count ($_POST['selectedObjTypes']) !== count ($gvSelection)) {
            
            throw new CHttpException (400, Yii::t('app', 'Bad Request'));
        }
        $selectedObjs = $_POST['selectedObjs'];
        $selectedObjTypes = $_POST['selectedObjTypes'];

        if (!isset ($_POST['targetFolder']) || $_POST['targetFolder'] === '') {
            $destination = null;
        } else  {
            $targetFolder = $_POST['targetFolder'];
            $destination = DocFolders::model ()->findByPk ($targetFolder);
            if (!$destination)
                throw new CHttpException (400, Yii::t('app', 'Folder not found'));
            if (!Yii::app()->controller->checkPermissions ($destination, 'edit')) {
                self::$errorFlashes[] = 
                    Yii::t('app', 'You do not have permission to edit this folder.');
                return 0;
            }
        }

        $objCount = count ($gvSelection);
        $successes = 0;
        for ($i = 0; $i < $objCount; $i++) {
            $id = $selectedObjs[$i];
            if (((int) $id) === DocFolders::TEMPLATES_FOLDER_ID) {
                continue;
            }
            $type = $selectedObjTypes[$i];
            if ($type === 'doc') {
                $obj = Docs::model ()->findByPk ($id);
            } elseif ($type === 'folder') {
                $obj = DocFolders::model ()->findByPk ($id);
            } else {
                self::$errorFlashes[] = Yii::t('app', 'Invalid object type.');
                continue;
            }
            if (!$obj) {
                self::$errorFlashes[] = 
                    Yii::t('app', 'Selected {type} does not exist', array (
                        '{type}' => $type === 'doc' ? ucfirst ($type) : $type,
                    ));
                continue;
            }
            if (!Yii::app()->controller->checkPermissions ($obj, 'edit')) {
                self::$errorFlashes[] = 
                    Yii::t('app', 'You do not have permission to edit this {type}.', array (
                        '{type}' => $type === 'doc' ? ucfirst ($type) : $type,
                    ));
                continue;
            }
            if ($obj instanceof DocFolders && $destination && $obj->id === $destination->id) {
                self::$errorFlashes[] = 
                    Yii::t('app', 'Cannot move "{name}" to a folder inside itself.', array (
                        '{name}' => $obj->name
                    ));
                continue;
            }
            if ($obj->moveTo ($destination)) {
                $successes++;
            } else {
                self::$errorFlashes[] = 
                    Yii::t('app', 'Failed to move "{name}"', array (
                        '{name}' => $obj->name
                    ));
            }
        }
        if ($successes) {
            self::$successFlashes[] = Yii::t(
                'app', 
                '{n} object moved to "{destination}"|{n} objects moved to "{destination}"', array(
                    $successes,
                    '{destination}' => $destination ?  
                        $destination->name :
                        Yii::t('docs', 'Docs') 
                )
            );
        }
        return $successes;
    }

}

?>
