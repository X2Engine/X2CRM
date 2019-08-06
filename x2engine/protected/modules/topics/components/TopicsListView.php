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





Yii::import ('zii.widgets.CListView');

class TopicsListView extends CListView {

    /**
     * @var CModel $model
     */
    public $model; 

    public function renderWidgets () {
        $layoutManager = $this->widget ('RecordViewLayoutManager', array ('staticLayout' => false));
        Yii::app()->controller->widget('X2WidgetList', array(
            'model' => $this->model,
            'layoutManager' => $layoutManager,
            'params' => array (
                'columnCount' => 1,
                'widgetType' => 'topics',
            )
        ));
    }

    public function renderButtons () {
        $stickyButton  = '';
        if(Yii::app()->user->checkAccess('TopicsPinUnpinTopic')){
            $stickyButton = X2Html::link(
                $this->model->sticky ? 
                    Yii::t('topics','Unpin Topic') : 
                    Yii::t('topics','Pin Topic'), '#', 
                array(
                    'id' => 'sticky-topic', 'data-id' => $this->model->id, 'class' => 'x2-button',
                    'style'=>'vertical-align:top;'));
            Yii::app()->clientScript->registerScript('pin-topic','
                $(document).on("click","#sticky-topic",function(){
                    $.ajax({
                        url:"'.Yii::app()->controller->createUrl('/topics/topics/pinUnpinTopic').'",
                        data:{id:$(this).attr("data-id")},
                        beforeSend:function(){
                            x2.forms.inputLoading($("#sticky-topic"));
                        },
                        success:function(data){
                            $("#sticky-topic").html(data);
                            x2.forms.inputLoadingStop($("#sticky-topic"));
                        }
                    });
                    return false;
                });
            ',CClientScript::POS_READY);
        }

        echo 
            "<span class='list-view-title-bar-buttons'>
                $stickyButton
                <div id='show-topics-relationships-button' class='x2-button'>Relationships</div>
                <div id='show-topics-tags-button' class='x2-button'>Tags</div>
            </span>";
    }

}

?>
