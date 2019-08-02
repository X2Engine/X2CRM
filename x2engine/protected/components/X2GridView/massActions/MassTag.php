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






class MassTag extends BaseTagAction {

    public $hasButton = true; 

    protected $_label;

    /**
     * Renders the mass action dialog, if applicable
     * @param string $gridId id of grid view
     */
    public function renderDialog ($gridId, $modelName) {
        echo "
            <div class='mass-action-dialog' id='".$this->getDialogId ($gridId)."' 
             style='display: none;'>
                <div class='form'>
                    <div class='x2-tag-list'>
                        <span class='tag-container-placeholder'>".
                            Yii::t('app', 'Drag tags here from the tag cloud widget or click'.
                                ' to create a custom tag.')."
                        </span>
                    </div>
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
             class='fa fa-tag fa-lg mass-action-button x2-button mass-action-button-".
                get_class ($this)."'>
            </a>";
    }


    /**
     * @return string label to display in the dropdown list
     */
    public function getLabel () {
        if (!isset ($this->_label)) {
            $this->_label = Yii::t('app', 'Tag selected');
        }
        return $this->_label;
    }

    public function getPackages () {
        return array_merge (parent::getPackages (), array (
            'X2MassTag' => array(
                'baseUrl' => Yii::app()->request->baseUrl,
                'js' => array(
                    'js/X2GridView/MassTag.js',
                ),
                'depends' => array ('BaseTagAction'),
            ),
        ));
    }

    protected function addSuccessFlash ($tagsTouched, $updatedRecords) {
        self::$successFlashes[] = Yii::t(
            'app', '{tagsAdded} tag'.($tagsTouched === 1 ? '' : 's').
                ' added to {updatedRecordsNum} record'.($updatedRecords === 1 ? '' : 's'),
                array (
                    '{updatedRecordsNum}' => $updatedRecords,
                    '{tagsAdded}' => $tagsTouched
                )
        );
    }

    protected function addNoticeFlash ($recordId, $tag) {
        self::$noticeFlashes[] = Yii::t(
            'app', 'Record {recordId} could not be tagged with {tag}. This record '.
                'may already have this tag.', array (
                '{recordId}' => $recordId, '{tag}' => $tag
            )
        );
    }

    protected function tagAction (CActiveRecord $model, $tag) {
        return $model->addTags ($tag);
    }

}
