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




Yii::import('application.components.X2GridView.massActions.*');

class X2GridViewMassActionAction extends CAction {
    
    /**
     * Mass action names and mass action class names
     */
    private $massActionClasses = array (
        'MassAddToList',
        'MassCompleteAction',
        'MassRemoveFromList',
        'MassUncompleteAction',
        'NewListFromSelection',
        'MassMoveFileSysObjToFolder',
        'MassRenameFileSysObj',
         
        'MassAddRelationship',
        'MassAssociateEmails',
        'MassConvertRecord',
        'MassDelete',
        'MassEmailDelete',
        'MassMarkAsRead',
        'MassMarkAsUnread',
        'MassMoveToFolder',
        'MassPublishAction',
        'MassPublishCall',
        'MassPublishNote',
        'MassPublishTime',
        'MassTag',
        'MassTagRemove',
        'MassUpdateFields',
        'MergeRecords',
        'MassExecuteMacro',
         
    );

    private $_massActions;

    /**
     * @return array instances of mass action objects indexed by mass action name
     */
    public function getMassActionInstances () {
        if (!isset ($this->_massActions)) {
            $this->_massActions = array ();
            foreach ($this->massActionClasses as $class) {
                $this->_massActions[$class] = new $class;
            }
        }
        return $this->_massActions;
    }

    /**
     * Execute specified mass action on specified records
     */
    public function run(){
        if (Yii::app()->user->isGuest) {
            Yii::app()->controller->redirect(Yii::app()->controller->createUrl('/site/login'));
        }

        if (Yii::app()->request->getRequestType () === 'GET') {
            $_POST = $_GET;
        }

        if (isset ($_POST['passConfirm']) && $_POST['passConfirm']) {
            MassAction::superMassActionPasswordConfirmation ();
            return;
        }
        if (!isset ($_POST['massAction']) || 
            ((!isset ($_POST['superCheckAll']) || !$_POST['superCheckAll']) &&
             (!isset ($_POST['gvSelection']) || !is_array ($_POST['gvSelection'])))) {

            /**/AuxLib::debugLogR ('run error');
            throw new CHttpException (400, Yii::t('app', 'Bad Request'));
        }
        $massAction = $_POST['massAction'];
        $massActionInstance = $this->getInstanceFor ($massAction);

        if (isset ($_POST['superCheckAll']) && $_POST['superCheckAll']) {
            $uid = $_POST['uid'];
            $idChecksum = $_POST['idChecksum'];
            $totalItemCount = intval ($_POST['totalItemCount']);
            $massActionInstance->superExecute ($uid, $totalItemCount, $idChecksum);
        } else {
            $gvSelection = $_POST['gvSelection'];
            if ($massActionInstance->beforeExecute ()) {
                $massActionInstance->execute ($gvSelection);
            }
            $massActionInstance::echoResponse ();
        }
    }

    /**
     * validates mass action name and returns MassAction instance that corresponds with it
     * @param string $massAction
     */
    private function getInstanceFor ($massAction) {
        $instances = $this->getMassActionInstances ();
        if (!in_array ($massAction, array_keys ($instances))) {
            /**/AuxLib::debugLogR ('invalid mass action '.$massAction);
            throw new CHttpException (400, Yii::t('app', 'Bad Request'));
        }
        return $instances[$massAction];
    }

}

?>
