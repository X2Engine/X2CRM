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






abstract class BaseTagAction extends MassAction {

    protected $_label;

    public function getPackages () {
        return array_merge (parent::getPackages (), array (
            'BaseTagAction' => array(
                'baseUrl' => Yii::app()->request->baseUrl,
                'js' => array(
                    'js/X2Tags/TagContainer.js',
                    'js/X2Tags/TagCreationContainer.js',
                    'js/X2Tags/MassActionTagsContainer.js',
                    'js/X2GridView/BaseTagAction.js',
                ),
                'depends' => array ('X2MassAction'),
            ),
        ));
    }

    public function execute (array $gvSelection) {
        if (!isset ($_POST['tags']) || !is_array ($_POST['tags']) ||
            !isset ($_POST['modelType'])) {

            throw new CHttpException (400, Yii::t('app', 'Bad request.'));
            return;
        }
        $modelType = X2Model::model ($_POST['modelType']);
        if ($modelType === null) {
            throw new CHttpException (400, Yii::t('app', 'Invalid model type.'));
            return;
        }

        $updatedRecordsNum = 0;
        $tagsAdded = 0;
        foreach ($gvSelection as $recordId) {
            $model = $modelType->findByPk ($recordId);
            if ($model === null || !Yii::app()->controller->checkPermissions ($model, 'view')) 
                continue;
            $recordUpdated = false;
            foreach ($_POST['tags'] as $tag) {
                if (!$this->tagAction ($model, $tag)) {
                    $this->addNoticeFlash ($recordId, $tag);
                } else {
                    $tagsAdded++;
                    $recordUpdated = true;
                }
            }
            if ($recordUpdated) $updatedRecordsNum++;
        }

        if ($updatedRecordsNum > 0) {
            $this->addSuccessFlash ($tagsAdded, $updatedRecordsNum);
        }
        return $updatedRecordsNum;
    }

    /**
     * @param int $tagsTouched   
     * @param string $updatedRecords
     */
    abstract protected function addSuccessFlash ($tagsTouched, $updatedRecords);

    /**
     * @param int $recordId   
     * @param string $tag   
     */
    abstract protected function addNoticeFlash ($recordId, $tag);

    /**
     * @param CActiveRecord $model   
     * @param string $tag   
     * @return bool true for success, false otherwise
     */
    abstract protected function tagAction (CActiveRecord $model, $tag);

}
