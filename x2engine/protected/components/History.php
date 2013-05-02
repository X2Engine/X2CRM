<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * California 95067, USA. or at email address contact@x2engine.com.
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
 *****************************************************************************************/

/**
 * Renders a CListView containing all actions associated with the specified model
 *
 * @package X2CRM.components
 */
class History extends X2Widget {
	public $associationType;		// type of record to associate actions with
	public $associationId = '';		// record to associate actions with

	public $historyType = 'all';

	public function run() {
		$historyTabs = array(
			'all'=>'All',
			'actions'=>'Actions',
			'comments'=>'Comments',
			'workflow'=>'Workflow',
			'attachments'=>'Attachments',
			'marketing'=>'Marketing',
		);

		if(isset($_GET['history']) && array_key_exists($_GET['history'],$historyTabs))
			$this->historyType = $_GET['history'];

		foreach($historyTabs as $type => &$label) {
			if($type == $this->historyType)
				$label = Yii::t('app',$label);
			else
				$label = CHtml::link(Yii::t('app',$label),'javascript:$.fn.yiiListView.update("history", {data: "history='.$type.'"})');
		}

		$historyTabs['collapse'] = '<a href="#" id="history-collapse" onclick="javascript:$(\'#history .description\').toggle();">[&ndash;]</a>';


		$this->widget('zii.widgets.CListView', array(
			'id'=>'history',
			'dataProvider'=>$this->getHistory(),
			'itemView'=>'application.modules.actions.views.actions._view',
			'htmlOptions'=>array('class'=>'action list-view'),
			'template'=>'<div class="publisher-tabs">'.implode(' | ',array_values($historyTabs)).'</div> {sorter}{items}{pager}',
		));
	}

	public function getHistory() {

		$historyCriteria = array(
			'all'=>'',
			'actions'=>' AND type IS NULL',
			'workflow'=>' AND type="workflow"',
			'comments'=>' AND type="note"',
			'attachments'=>' AND type="attachment"',
			'marketing'=>' AND type IN ("email","webactivity","weblead","email_staged","email_opened","email_clicked","email_unsubscribed")',
		);
        
		return new CActiveDataProvider('Actions',array(
			'criteria'=>array(
				'order'=>'IF(complete="No", GREATEST(createDate, IFNULL(dueDate,0), IFNULL(lastUpdated,0)), GREATEST(createDate, IFNULL(completeDate,0), IFNULL(lastUpdated,0))) DESC',
				'condition'=>'associationId='.$this->associationId.' AND associationType="'.$this->associationType.'"
					AND (visibility="1" OR assignedTo="'.Yii::app()->user->getName().'")'.$historyCriteria[$this->historyType]
			)
		));
	}
}








