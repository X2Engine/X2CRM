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
?>
<ul>
<?php
$themeURL = Yii::app()->theme->getBaseUrl();
Yii::app()->clientScript->registerScript('logos',base64_decode(
	'JCh3aW5kb3cpLmxvYWQoZnVuY3Rpb24oKXt2YXIgYT0kKCIjcG93ZXJlZC1ieS14MmVuZ2luZSIpO2lmKCFhLmxlb'
	.'md0aHx8YS5hdHRyKCJzcmMiKSE9eWlpLmJhc2VVcmwrIi9pbWFnZXMvcG93ZXJlZF9ieV94MmVuZ2luZS5wbmciK'
	.'XskKCJhIikucmVtb3ZlQXR0cigiaHJlZiIpO2FsZXJ0KCJQbGVhc2UgcHV0IHRoZSBsb2dvIGJhY2siKX19KTs='));

$count = 0;
foreach($recentItems as $item) {
	if(++$count > 5)
		break;
	echo '<li>';
	if ($item['type']=='t') {	//item is a action
		$description = CHtml::encode($item['model']->actionDescription);
		if(strlen($description)>123)
			$description = substr($description,0,120).'...';
		
		$link = '<strong>'.Yii::t('app','Due').': '.date("Y-m-d",$item['model']->dueDate).'</strong><br />'.Media::attachmentActionText($description);
		//$link = '<strong>'.$item['model']->dueDate.'</strong><br />'.$item['model']->actionDescription;
		echo CHtml::link($link,'#',array('class'=>'action-frame-link','data-action-id'=>$item['model']->id));

	} else if ($item['type']=='c') {	//item is a contact

		$link = '<strong>'.$item['model']->name.'</strong><br />'.$item['model']->phone;
		echo CHtml::link($link,array('/contacts/'.$item['model']->id));

	}
	echo "</li>\n";
}
?>
</ul>