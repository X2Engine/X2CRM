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




class NewListFromSelection extends MassAction {

    protected $_label;

    private $listId;

    /**
     * Renders the mass action dialog, if applicable
     * @param string $gridId id of grid view
     */
    public function renderDialog ($gridId, $modelName) {
        echo "
            <div class='mass-action-dialog' 
             id='".$this->getDialogId ($gridId)."' style='display: none;'>
                <span>".
                    Yii::t('app', 'What should the list be named?')."
                </span>
                <br/>
                <input class='left new-list-name'></input>
            </div>";
    }

    /**
     * @return string label to display in the dropdown list
     */
    public function getLabel () {
        if (!isset ($this->_label)) {
            $this->_label = Yii::t('app', 'New list from selected');
        }
        return $this->_label;
    }

    public function getPackages () {
        return array_merge (parent::getPackages (), array (
            'X2NewListFromSelection' => array(
                'baseUrl' => Yii::app()->request->baseUrl,
                'js' => array(
                    'js/X2GridView/NewListFromSelection.js',
                ),
                'depends' => array ('X2MassAction'),
            ),
        ));
    }

    public function execute (array $gvSelection) {
        if ((Yii::app()->controller->modelClass !== 'Contacts' 
                && Yii::app()->controller->modelClass !== 'X2Leads'
                && Yii::app()->controller->modelClass !== 'Accounts'
                && Yii::app()->controller->modelClass !== 'Opportunity')||
            !isset ($_POST['listName']) || $_POST['listName'] === '') {
            
            throw new CHttpException (400, Yii::t('app', 'Bad Request at execute'));
        }
        if (!Yii::app()->params->isAdmin && 
            !Yii::app()->user->checkAccess ('ContactsCreateListFromSelection')) {

            return -1;
        }
        
        $listName = $_POST['listName'];
        foreach($gvSelection as &$contactId){
            if(!ctype_digit((string) $contactId))
                throw new CHttpException(400, Yii::t('app', 'Invalid selection.'));
        }

        $list = new X2List;
        $list->name = $_POST['listName'];
        $list->modelName = Yii::app()->controller->modelClass;
        $list->type = 'static';
        $list->assignedTo = Yii::app()->user->getName();
        $list->visibility = 1;
        $list->createDate = time();
        $list->lastUpdated = time();

        $itemModel = X2Model::model($list->modelName);
        $success = true;
        if($list->save()){ // if the list is valid save it so we can get the ID
            $count = 0;
            foreach($gvSelection as &$itemId){

                if($itemModel->exists('id="'.$itemId.'"')){ // check if contact exists
                    $item = new X2ListItem;
                    $item->contactId = $itemId;
                    $item->listId = $list->id;
                    if($item->save()) // add all the things!
                        $count++;
                }
            }
            $list->count = $count;
            $this->listId = $list->id;
            if($list->save()) {
                self::$successFlashes[] = Yii::t(
                    'app', '{count} record'.($count === 1 ? '' : 's').
                        ' added to new list "{list}"', array (
                            '{count}' => $count,
                            '{list}' => $list->name,
                        )
                );
            } else {
                self::$errorFlashes[] = Yii::t(
                    'app', 'List created but records could not be added to it');
            }
        } else {
            $success = false;
            self::$errorFlashes[] = Yii::t(
                'app', 'List could not be created');
        }
        return $success ? $count : -1;

    }

    /**
     * Add list id to response data so that subsequent client requests can be for add to list 
     * mass action
     */
    protected function generateSuperMassActionResponse ($successes, $selectedRecords, $uid) {
        $response = parent::generateSuperMassActionResponse ($successes, $selectedRecords, $uid);
        $response['listId'] = $this->listId;
        return $response;
    }

}
