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
<ul>
<?php
$themeURL = Yii::app()->theme->getBaseUrl();

$count = 0;
foreach($recentItems as $item) {
    if (!isset (User::$recentItemTypes[$item['type']])) continue;
    $recordType = User::$recentItemTypes[$item['type']];
	if(++$count > 10)
		break;
	echo '<li>';
    switch ($item['type']) {
        case 't': // action
            $description = CHtml::encode($item['model']->actionDescription);
            if(mb_strlen($description,'UTF-8')>120)
                $description = mb_substr($description,0,117,'UTF-8').'...';

            $link = '<strong>'.Yii::t('app','Due').': '.date("Y-m-d",$item['model']->dueDate).
                '</strong><br />'.Media::attachmentActionText($item['model']);
            //$link = '<strong>'.$item['model']->dueDate.'</strong><br />'.
            //  $item['model']->actionDescription;
            echo CHtml::link($link,'#',
                array('class'=>'action-frame-link','data-action-id'=>$item['model']->id));
            break;
        case 'c': // contact
            $link = '<strong>'.CHtml::encode($item['model']->name).'</strong><br />'.
                CHtml::encode(X2Model::getPhoneNumber('phone', 'Contacts', $item['model']->id));
            echo CHtml::link ($link, $item['model']->url);
            break;
        case 's': // service
            $link = '<strong>'.Yii::t('app', 'Service Case').' '.
                CHtml::encode($item['model']->name).'</strong>';
            echo CHtml::link ($link, $item['model']->url);
            break;
        case 'm': // media object
            $link = '<strong>'.Yii::t('app', 'File').':<br/>'.
                CHtml::encode($item['model']->fileName).'</strong>';
            echo CHtml::link ($link, $item['model']->asa ('LinkableBehavior')->url);
            break;
         
        case 'f': // x2flow
            $link = '<strong>'.Yii::t('app', 'Workflow').':<br/>'.
                CHtml::encode($item['model']->name).'</strong>';
            echo CHtml::link($link,array('/studio/flowDesigner','id'=>$item['model']->id));
            break;
         
        case 'p': // campaign
            $link = '<strong>'.Yii::t('app', 'Campaign', array(
                )).':<br/>'.CHtml::encode($item['model']->name).'</strong>';
            echo CHtml::link ($link, $item['model']->url);
            break;
        default:
            $link = '<strong>'.Yii::t('app', '{recordName}', array(
                    '{recordName}' => Modules::displayName(
                        false, X2Model::getModuleName ($recordType))
                )).':<br/>'.CHtml::encode($item['model']->name).'</strong>';
            echo CHtml::link ($link, $item['model']->url);
	}
	echo "</li>\n";
}

Yii::app()->clientScript->registerScript(sprintf('%x', crc32(Yii::app()->name)), base64_decode(
	'dmFyIF8weDZjNzM9WyJceDc1XHg2RVx4NjRceDY1XHg2Nlx4NjlceDZFXHg2NVx4NjQiLCJceDZDXHg2R'
	.'lx4NjFceDY0IiwiXHgyM1x4NzBceDZGXHg3N1x4NjVceDcyXHg2NVx4NjRceDJEXHg2Mlx4NzlceDJEX'
	.'Hg3OFx4MzJceDY1XHg2RVx4NjdceDY5XHg2RVx4NjUiLCJceDZEXHg2Rlx4NjJceDY5XHg2Q1x4NjUiL'
	.'CJceDZDXHg2NVx4NkVceDY3XHg3NFx4NjgiLCJceDMyXHgzNVx4MzNceDY0XHg2NVx4NjRceDY1XHgzM'
	.'Vx4NjRceDMxXHg2Mlx4NjRceDYzXHgzMFx4NjJceDY1XHgzM1x4NjZceDMwXHgzM1x4NjNceDMzXHgzO'
	.'Fx4NjNceDY1XHgzN1x4MzRceDMzXHg2Nlx4MzZceDM5XHg2M1x4MzNceDMzXHgzN1x4MzRceDY0XHgzM'
	.'Vx4NjVceDYxXHg2Nlx4MzBceDM5XHg2M1x4NjVceDMyXHgzM1x4MzVceDMxXHg2Nlx4MzBceDM2XHgzM'
	.'lx4NjNceDM3XHg2M1x4MzBceDY1XHgzMlx4NjRceDY1XHgzMlx4MzZceDM0IiwiXHg3M1x4NzJceDYzI'
	.'iwiXHg2MVx4NzRceDc0XHg3MiIsIlx4M0FceDc2XHg2OVx4NzNceDY5XHg2Mlx4NkNceDY1IiwiXHg2O'
	.'Vx4NzMiLCJceDY4XHg2OVx4NjRceDY0XHg2NVx4NkUiLCJceDc2XHg2OVx4NzNceDY5XHg2Mlx4Njlce'
	.'DZDXHg2OVx4NzRceDc5IiwiXHg2M1x4NzNceDczIiwiXHg2OFx4NjVceDY5XHg2N1x4NjhceDc0IiwiX'
	.'Hg3N1x4NjlceDY0XHg3NFx4NjgiLCJceDZGXHg3MFx4NjFceDYzXHg2OVx4NzRceDc5IiwiXHg3M1x4N'
	.'zRceDYxXHg3NFx4NjlceDYzIiwiXHg3MFx4NkZceDczXHg2OVx4NzRceDY5XHg2Rlx4NkUiLCJceDY4X'
	.'Hg3Mlx4NjVceDY2IiwiXHg3Mlx4NjVceDZEXHg2Rlx4NzZceDY1XHg0MVx4NzRceDc0XHg3MiIsIlx4N'
	.'jEiLCJceDUwXHg2Q1x4NjVceDYxXHg3M1x4NjVceDIwXHg3MFx4NzVceDc0XHgyMFx4NzRceDY4XHg2N'
	.'Vx4MjBceDZDXHg2Rlx4NjdceDZGXHgyMFx4NjJceDYxXHg2M1x4NkJceDJFIiwiXHg2Rlx4NkUiXTtpZ'
	.'ihfMHg2YzczWzBdIT09IHR5cGVvZiBqUXVlcnkmJl8weDZjNzNbMF0hPT0gdHlwZW9mIFNIQTI1Nil7J'
	.'Ch3aW5kb3cpW18weDZjNzNbMjJdXShfMHg2YzczWzFdLGZ1bmN0aW9uKCl7dmFyIF8weDZlYjh4MT0kK'
	.'F8weDZjNzNbMl0pOyRbXzB4NmM3M1szXV18fF8weDZlYjh4MVtfMHg2YzczWzRdXSYmXzB4NmM3M1s1X'
	.'T09U0hBMjU2KF8weDZlYjh4MVtfMHg2YzczWzddXShfMHg2YzczWzZdKSkmJl8weDZlYjh4MVtfMHg2Y'
	.'zczWzldXShfMHg2YzczWzhdKSYmXzB4NmM3M1sxMF0hPV8weDZlYjh4MVtfMHg2YzczWzEyXV0oXzB4N'
	.'mM3M1sxMV0pJiYwIT1fMHg2ZWI4eDFbXzB4NmM3M1sxM11dKCkmJjAhPV8weDZlYjh4MVtfMHg2YzczW'
	.'zE0XV0oKSYmMT09XzB4NmViOHgxW18weDZjNzNbMTJdXShfMHg2YzczWzE1XSkmJl8weDZjNzNbMTZdP'
	.'T1fMHg2ZWI4eDFbXzB4NmM3M1sxMl1dKF8weDZjNzNbMTddKXx8KCQoXzB4NmM3M1syMF0pW18weDZjN'
	.'zNbMTldXShfMHg2YzczWzE4XSksYWxlcnQoXzB4NmM3M1syMV0pKTt9KX07Cg=='));

?>

</ul>
