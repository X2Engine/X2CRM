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




class MobileHistoryItem extends CComponent {
    public $action; // Actions model

    public function getTemplate () {
        return '<div class="record-list-item" data-x2-action-type="'.
            X2Html::sanitizeAttribute ($this->action['type']).'">
                <div class="icon-container">
                    {icon}
                </div>
                <div class="history-item-content-container-outer">
                    <div class="history-item-date-line"> 
                        {dateLine} {deleteItem}
                    </div>
                    <div class="history-item-content"> 
                        {content}
                    </div>
                    <div class="history-item-author"> 
                        {author}
                    </div>
                </div>
        </div>';
    }

	public function __construct () {
        $this->attachBehaviors ($this->behaviors ());
	}

    public function behaviors () {
        return array (
            'WidgetTemplateBehavior' => array (
                'class' => 'application.components.behaviors.WidgetTemplateBehavior'
            ),
        );
    }

    public function renderDeleteItem () {
        return '<a class="delete-button requires-confirmation" '
            . 'href="'.Yii::app()->createAbsoluteUrl ('actions/mobileDelete',
            array('id'=>$this->action->id,)).'">'.X2Html::fa ("fa-trash").'</a>'
            .            '<div class="confirmation-text" style="display: none;">

                Are you sure you want to delete this?
            </div>';
    }
    
    public function renderDateLine () {
        return Yii::app()->dateFormatter->formatDateTime (
            $this->action->createDate, 'medium', 'short');
    }

    public function renderAuthor () {
        if($this->action->complete == 'Yes'){
            return $this->action->renderAttribute ('completedBy', true, false, false);
        } else {
            return $this->action->renderAttribute ('assignedTo', true, false, false);
        }
    }

    public function renderContent () {
        return $this->action->actionDescription;
    }

    public function renderIcon () {
        if (empty($this->action->type)) {
            if ($this->action->complete == 'Yes') {
                $type = 'complete';
            } else {
                $type = 'action';
            }
        } else {
            if ($this->action->type === 'emailFrom') {
                $type = 'email';
            } else {
                $type = $this->action->type;
            }
        }
        return '
            <div class="icon '.$type.'">
            <div class="stacked-icon"></div></div>';
    }

    public function render () {
        return $this->renderTemplate ();
    }

}

?>
