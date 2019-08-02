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






$this->pageTitle = $model->name;

$authParams['X2Model'] = $model;

$menuOptions = array(
    'all', 'create', 'lists', 'newsletters', 'view', 'edit', 'delete', 'weblead', 'webtracker',
);

$plaOptions = array(
    'anoncontacts', 'fingerprints'
);
$menuOptions = array_merge($menuOptions, $plaOptions);

$this->insertMenu($menuOptions, $model, $authParams);


?>


<?php
foreach(Yii::app()->user->getFlashes() as $key => $message) {
    echo '<div class="flash-' . $key . '">' . $message . "</div>\n";
} ?>

<div>
<?php
$this->widget('zii.widgets.grid.CGridView', array(
    'id'=>'contacts-grid',
    'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
    'template'=> '<div class="page-title icon marketing"><h2>'.CHtml::encode(CHtml::encode($model->name)).'</h2><div class="right" style="padding:5px">'
        .CHtml::link(
            Yii::t('marketing','Email Entire List'),
            Yii::app()->createUrl('/marketing/marketing/create',array('Campaign[listId]'=>$model->nameId)),
            array('class'=>'x2-button')
        )
        .' <a class="x2-button" href="javascript:void(0);" '.
          'onclick="$(\'html,body\').animate({scrollTop: $(\'#webform\').offset().top});">'
          .Yii::t('marketing','Create Web Form') 
        .'</a></div>'
        .'<div class="title-bar">{summary}</div></div>{items}{pager}',
    'dataProvider'=>$model->statusDataProvider(20),
    'columns'=>array(
        array(
            'name'=>'emailAddress',
            'header'=>Yii::t('contacts','Email'),
            'headerHtmlOptions'=>array('style'=>'width: 20%;'),
        ),    
        array(
            'name'=>'name',
            'header'=>Yii::t('contacts','Name'),
            'headerHtmlOptions'=>array('style'=>'width: 15%;'),
            'value'=>'CHtml::link($data["firstName"] . " " . '.
                '$data["lastName"],array("/contacts/contacts/view","id"=>$data["contactId"]))',
            'type'=>'raw',
        ),
        array(
            'header'=>Yii::t('marketing','Unsubscribed'),
            'class'=>'CCheckBoxColumn',
            'checked'=>'$data["unsubscribed"] != 0',
            'selectableRows'=>0,
            'htmlOptions'=>array('style'=>'text-align: center;'),
            'headerHtmlOptions'=>array('style'=>'width: 9%;')
        ),
        array(
            'name'=>'remove',
            'header'=>'',
            'value'=>'CHtml::link ("<div class=\'fa fa-times x2-delete-icon\'></div>",
                array("removeFromList",
                    "email" => $data["emailAddress"],
                    "lid" => $data["listId"]),
                array(
                    "confirm" => Yii::t("marketing", "Are you sure you want to remove this ".
                        "email address from the list?")
                ))',
            'type'=>'raw',
        ),
    ),
));
?>
</div>

<div style="float:left" margin-top:" 23px;">
<a id="webform"></a>
<?php 
if(!Yii::app()->user->checkAccess('MarketingAdminAccess')) {
    $condition = ' AND t.visibility="1" OR t.assignedTo="Anyone" OR t.assignedTo="'.
        Yii::app()->user->getName().'"';
    /* x2temp */
    $groupLinks = 
        Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where(
            'userId='.Yii::app()->user->getId())->queryColumn();
    if(!empty($groupLinks)) {
        $condition .= ' OR t.assignedTo IN ('.implode(',',$groupLinks).')';
    }

    $condition .= ' OR (t.visibility=2 AND t.assignedTo IN 
        (SELECT username FROM x2_group_to_user WHERE groupId IN
        (SELECT groupId FROM x2_group_to_user WHERE userId='.Yii::app()->user->getId().')))';
} else {
    $condition='';
}
$forms = WebForm::model()->findAll('type="weblist"'.$condition);

$this->widget('application.components.WebFormDesigner.WebListFormDesigner', array(
        'forms' => $forms,
        'id' => $model->id
    ));
?>
</div>

<div class="span-12" id='history' style="padding: 15px;">
<?php

if (isset($_GET['history'])) {
    $history = $_GET['history'];
} else {
    $history = "all";
}

$this->widget('zii.widgets.CListView', array(
    'id'=>'campaign-history',
    'dataProvider'=>$this->getHistory($model),
    'itemView'=>'application.modules.actions.views.actions._view',
    'htmlOptions'=>array('class'=>'action list-view'),
    'template'=> 
        ($history == 'all' ? '<h3>'.Yii::t('app','History')."</h3>" : 
            CHtml::link(
                Yii::t('app','History'),
                'javascript:$.fn.yiiListView.update("campaign-history", {data: "history=all"})')).
        " | ".
        ($history=='actions' ? '<h3>'.Yii::t('app','Actions')."</h3>" : 
            CHtml::link(
                Yii::t('app','Actions'),
                'javascript:$.fn.yiiListView.update("campaign-history", '.
                '{data: "history=actions"})')).
        " | ".
        ($history=='comments' ? '<h3>'.Yii::t('app','Comments')."</h3>" : 
            CHtml::link(
                Yii::t('app','Comments'),
                'javascript:$.fn.yiiListView.update("campaign-history", '.
                '{data: "history=comments"})')).
        " | ".
        ($history=='attachments' ? '<h3>'.Yii::t('app','Attachments')."</h3>" : 
            CHtml::link(
                Yii::t('app','Attachments'),
                'javascript:$.fn.yiiListView.update("campaign-history", '.
                '{data: "history=attachments"})')).
        '</h3>{summary}{sorter}{items}{pager}',
));
?>
</div>
