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



 ?>

<?php
$users = User::getNames();
$form=$this->beginWidget('CActiveForm', array(
    'enableAjaxValidation'=>false,
));
?>

<style type="text/css">

.dialog-label {
	font-weight: bold;
	display: block;
}

.cell {
	float: left;
}

.dialog-cell {
	padding: 5px;
}

#calendar-invites tr,th{
        font-weight: bold;
        padding: 5px;
    }
    
    #calendar-invites tr,td{
        padding: 5px;
    }

</style>

<div class="row">
	<div class="cell dialog-cell" style="float: none;">
		<?php echo $model->actionDescription; ?>
	</div>
</div>

<div class="row">
	<div class="cell dialog-cell">
		<?php 
        echo $form->label($model,($isEvent?'startDate':'dueDate'), array('class'=>'dialog-label'));
		echo Formatter::formatDateTime($model->dueDate);	//format date from DATETIME

		if($isEvent) {
			echo $form->label($model, 'endDate', array('class'=>'dialog-label'));
			echo Formatter::formatDateTime($model->completeDate);	//format date from DATETIME
		}

		echo $form->label($model, 'allDay', array('class'=>'dialog-label')); 
		echo $form->checkBox(
            $model, 'allDay', array('onChange'=>'giveSaveButtonFocus();', 'disabled'=>'disabled')); 
        ?>
	</div>

	<div class="cell dialog-cell">
		<?php 
        echo $form->label($model,'priority', array('class'=>'dialog-label')); 
		$priorityArray = Actions::getPriorityLabels();
		echo isset($priorityArray[$model->priority])?$priorityArray[$model->priority]:""; 
        ?>
	</div>
	<div class="cell dialog-cell">
		<?php
        // assigned to calendar instead of user?
		if($model->assignedTo == null && is_numeric($model->calendarId)) { 
		    $model->assignedTo = $model->calendarId;
		}
		echo $form->label($model,'assignedTo', array('class'=>'dialog-label')); 
        echo $model->renderAttribute (
            'assignedTo');
        ?>
    </div>
	<div class="cell dialog-cell">
        <?php
        if ($model->type === 'event') {
            if (!empty ($model->eventSubtype)) {
                echo $form->label ($model, 'eventSubtype', array ('class' => 'dialog-label'));
                echo $model->renderAttribute ('eventSubtype');
            }
            if (!empty ($model->eventStatus)) {
                echo $form->label ($model, 'eventStatus', array ('class' => 'dialog-label'));
                echo $model->renderAttribute ('eventStatus');
            }
        }
		?>
</div>
    <?php if (!empty($model->invites)) { ?>
    <div style="clear:both"></div>
    <div class="row">
        <div class="cell dialog-cell">
            <table id="calendar-invites">
                <tr>
                    <th><?php echo Yii::t('calendar', 'Guest'); ?></th>
                    <th><?php echo Yii::t('calendar', 'Status'); ?></th>
                </tr>
                <?php
                foreach ($model->invites as $invite) {
                    echo "<tr>";
                    echo "<td>" . $invite->email . "</td><td>" . 
                            (is_null($invite->status) ? Yii::t('calendar', 'Awaiting response') 
                            : Yii::t('calendar', $invite->status)) . "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>
    </div>
<?php } ?>

<?php $this->endWidget(); ?>
