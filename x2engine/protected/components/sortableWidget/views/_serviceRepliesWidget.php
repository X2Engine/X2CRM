<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2022 X2 Engine Inc.
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




Yii::app()->clientScript->registerScript('submitNewReply',"
    $('#service-reply-btn').click(function(){
        let txt = $('#service-reply-txt').val();
        let url = '". Yii::app()->controller->createUrl('/services/services/newReply')."'
        if (!txt) return;

        let failureMsg = 'Message could not be sent. Please try again later.';
        $.post(url, {
            'ServiceReplies': {'serviceId': $model->id, 'text': txt},
        }).done(function (data) {
            if (data) { 
                result = JSON.parse(data);
                if (!$.isNumeric(result)) {
                    console.log('action errors: ', result);
                    alert(failureMsg);
                } else {
                    $.fn.yiiListView.update('reply-listview');
                    $('#service-reply-txt').val('');
                }
            } else {
                console.log('empty response');
                alert(failureMsg);
            }
        }).fail(function (data, textStatus, errorThrown) {
            console.log('post fail: ', data, textStatus, errorThrown);
            alert(failureMsg);
        })

    });
", CClientScript::POS_END);
?>

<div>
    <?php echo CHtml::button(Yii::t('app', 'Submit'), ['id'=>'service-reply-btn', 'class'=>'x2-button']);?>
    <textarea id="service-reply-txt" placeholder="<?php echo Yii::t('common', 'Add Comment'); ?>"></textarea>
</div>

<?php

$dataProvider = new CActiveDataProvider('ServiceReplies', array(
    'id' => $model->id,
    'criteria' => ['condition'=>"serviceId = $model->id", 'order'=>'createDate DESC'],
    'pagination' => ['pageSize'=>10]
));

$this->widget('zii.widgets.CListView', array(
    'id'=>'reply-listview',
    'dataProvider'=>$dataProvider,
    'itemView'=>'application.modules.services.views.services._serviceReply',
    'pagerCssClass'=>'text-right list-inline',
    'pager'=> [
        'internalPageCssClass'=>'list-inline-item',
        'firstPageCssClass' => 'list-inline-item',
        'previousPageCssClass' => 'list-inline-item',
        'nextPageCssClass' => 'list-inline-item',
        'lastPageCssClass' => 'list-inline-item',
        'prevPageLabel' => '<',
        'nextPageLabel' => '>',
        'firstPageLabel' => '<<',
        'lastPageLabel' => '>>',
    ],
));
