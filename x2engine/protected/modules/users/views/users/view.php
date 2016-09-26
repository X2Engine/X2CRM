<?php
/***********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 **********************************************************************************/

$menuOptions = array(
    'feed', 'admin', 'create', 'invite', 'view', 'profile', 'edit', 'delete',
);
$this->insertMenu($menuOptions, $model);

?>
<div class="page-title icon users">
    <h2><span class="no-bold">
        <?php echo Yii::t('users','{user}:', array(
            '{user}' => Modules::displayName(false),
        )); ?></span> <?php echo CHtml::encode($model->firstName,' ',$model->lastName); ?></h2>
</div>
<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'baseScriptUrl'=>Yii::app()->theme->getBaseUrl().'/css/detailview',
	'attributes'=>array(
		'firstName',
		'lastName',
		empty($model->userAlias)?'username':'userAlias',
		'title',
		'department',
		'officePhone',
		'cellPhone',
		'homePhone',
		'address',
		'backgroundInfo',
		array(
			'name'=>'createDate',
			'type'=>'raw',
			'value'=>$model->createDate ? Formatter::formatDate($model->createDate) : "n/a",
		),
		'emailAddress',
		array(
			'name'=>'status',
			'type'=>'raw',
			'value'=>$model->status==1?"Active":"Inactive",
		),
	),
)); ?>
<br>
<div class="page-title rounded-top"><h2>
    <?php echo Yii::t('users','{action} History', array(
        '{action}' => Modules::displayName(false, "Actions"),
    )); ?>
</h2></div>


<?php
foreach($actionHistory as $action) {
    $association = $action->getAssociation();
    $associatedDescription = '';
    if ($association)
        $associatedDescription = (isset($association->backgroundInfo) ?
                        $association->backgroundInfo : $association->description);

	$this->widget('zii.widgets.CDetailView', array(
		'data'=>$action,
		'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/detailview',
		'attributes'=>array(
			array(
				'label'=>'Associated Record',
				'type'=>'raw',
                'value'=> ($action->getAssociationLink().(!empty($associatedDescription) ?
                    ': '.$associatedDescription : ''))
			),
			array(
				'label'=>'Action Description',
				'type'=>'raw',
				'value'=> in_array($action->type,Actions::$emailTypes)
                   ? CHtml::link(Yii::t('actions','View Email'),'javascript:void(0)',array(
                       'class' => 'action-frame-link',
                       'data-action-id' => $action->id,
                   ))
                   : CHtml::link($action->actionDescription,
                        		 array('/actions/actions/view','id'=>$action->id))
                
			),
			'assignedTo',
                        array(
                                'name'=>'dueDate',
				'label'=>'Due Date',
				'type'=>'raw',
				'value'=>date("F j, Y",$action->dueDate),
			),
			array(
				'label'=>'Complete',
				'type'=>'raw',
				'value'=>CHtml::tag("b",array(),CHtml::tag("font",$htmlOptions=array('color'=>'green'),CHtml::encode($action->complete)))
			),
			'priority',
			'type',
                        array(
                                'name'=>'createDate',
				'label'=>'Create Date',
				'type'=>'raw',
				'value'=>date("F j, Y",$action->createDate),
			),
            array(
                'name'=>'location',
				'label'=>'Location',
				'type'=>'raw',
                'value'=>((isset($action->location) && $association instanceof Contacts) ?
                    CHtml::link(Yii::t('contacts', 'View on Large Map'),
                    array(
                        'contacts/googleMaps',
                        'userId' => $model->id,
                        'noHeatMap' => 1,
                        'locationType' => array($action->location->type),
                    ))
                    : ''),
			),
		),
	));
    echo '<br />';
}
?><br /><br />
