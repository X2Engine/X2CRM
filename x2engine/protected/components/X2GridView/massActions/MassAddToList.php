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




class MassAddToList extends MassAction {

    protected $_label;

    /**
     * @return string label to display in the dropdown list
     */
    public function getLabel () {
        if (!isset ($this->_label)) {
            $this->_label = Yii::t('app', 'Add selected to list');
        }
        return $this->_label;
    }

    /**
     * Renders the mass action dialog, if applicable
     * @param string $gridId id of grid view
     */
    public function renderDialog ($gridId, $modelName) {
        $listNames = X2List::getAllStaticListNames (Yii::app()->controller);
        echo "
            <div class='mass-action-dialog' id='".$this->getDialogId ($gridId)."' 
             style='display: none;'>
                <span>".
                    Yii::t('app', 'Select a list to which the selected records will be added.')."
                </span>".
                (empty($listNames)
                    ? '<br><br>'.Yii::t('app','There are no static lists to which '
                            . 'contacts can be added.').' '.
                        CHtml::link(Yii::t('contacts','Create a List'),
                                    array('/contacts/contacts/createList'))
                    : CHtml::dropDownList ('addToListTarget', null, $listNames))."
            </div>";
    }

    public function getPackages () {
        return array_merge (parent::getPackages (), array (
            'X2AddToList' => array(
                'baseUrl' => Yii::app()->request->baseUrl,
                'js' => array(
                    'js/X2GridView/MassAddToList.js',
                ),
                'depends' => array ('X2MassAction'),
            ),
        ));
    }
    public function execute (array $gvSelection) {
        if (((Yii::app()->controller->modelClass !== 'Contacts') && (Yii::app()->controller->modelClass !== 'Accounts') && (Yii::app()->controller->modelClass !== 'Opportunity')
                && (Yii::app()->controller->modelClass !== 'X2Leads')) || !isset ($_POST['listId'])) {
            throw new CHttpException (400, Yii::t('app', 'Bad Request'));
        }
        $listId = $_POST['listId'];

        foreach($gvSelection as &$contactId) {
            if(!ctype_digit((string) $contactId)) {
                throw new CHttpException (400, Yii::t('app', 'Bad Request'));
            }
        }

        $list = CActiveRecord::model('X2List')->findByPk($listId);
        $updatedRecordsNum = sizeof ($gvSelection);
        $success = true;
        
        //make sure list type is the same as those being added
        if(Yii::app()->controller->modelClass !== $list->modelName) {
             throw new CHttpException (400, Yii::t('app', 'List is of diffrent type'));
        }
        // check permissions
        if ($list !== null && Yii::app()->controller->checkPermissions ($list, 'edit')) {
            if ($list->addIds($gvSelection)) {
                self::$successFlashes[] = array (
                    'message' => Yii::t(
                        'app', '{updatedRecordsNum} record'.($updatedRecordsNum === 1 ? '' : 's').
                            ' added to list "{list}"', array (
                                '{updatedRecordsNum}' => $updatedRecordsNum,
                                '{list}' => $list->link,
                            )
                    ), 'encode' => false);
                //self::$successFlashes['fade'] = 0;
            } else {
                $success = false;
                self::$errorFlashes[] = Yii::t(
                    'app', 'The selected record'.($updatedRecordsNum === 1 ? '' : 's').
                        ' could not be added to this list');
            }
        } else {
            $success = false;
            self::$errorFlashes[] = Yii::t(
                'app', 'You do not have permission to modify this list');
        }
        return $success ? $updatedRecordsNum : 0;

    }

}
