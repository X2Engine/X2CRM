<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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

$count = 0;
foreach($recentItems as $item) {
	if(++$count > 10)
		break;
	echo '<li>';
    switch ($item['type']) {
        case 't': // action
            $description = CHtml::encode($item['model']->actionDescription);
            if(mb_strlen($description,'UTF-8')>120)
                $description = mb_substr($description,0,117,'UTF-8').'...';

            $link = '<strong>'.Yii::t('app','Due').': '.date("Y-m-d",$item['model']->dueDate).
                '</strong><br />'.Media::attachmentActionText($description);
            //$link = '<strong>'.$item['model']->dueDate.'</strong><br />'.$item['model']->actionDescription;
            echo CHtml::link($link,'#',
                array('class'=>'action-frame-link','data-action-id'=>$item['model']->id));
            break;
        case 'c': // contact
            $link = '<strong>'.CHtml::encode($item['model']->name).'</strong><br />'.CHtml::encode(X2Model::getPhoneNumber('phone', 'Contacts', $item['model']->id));
            echo CHtml::link($link,array('/contacts/contacts/view','id'=>$item['model']->id));
            break;
        case 'a': // account
            $link = '<strong>'.Yii::t('app', '{Account}', array(
                    '{Account}'=>Modules::displayName(false, 'Accounts')
                )).':<br/>'.CHtml::encode($item['model']->name).'</strong><br />'.
                CHtml::encode($item['model']->phone);
            echo CHtml::link($link,array('/accounts/accounts/view','id'=>$item['model']->id));
            break;
        case 'p': // campaign
            $link = '<strong>'.Yii::t('app', 'Campaign').':<br/>'.CHtml::encode($item['model']->name).'</strong>';
            echo CHtml::link($link,array('/marketing/marketing/view','id'=>$item['model']->id));
            break;
        case 'o': // opportunity
            $link = '<strong>'.Yii::t('app', '{Opportunity}', array(
                    '{Opportunity}' => Modules::displayName(false, 'Opportunities')
                )).':<br/>'.CHtml::encode($item['model']->name).'</strong>';
            echo CHtml::link($link,array('/opportunities/opportunities/view','id'=>$item['model']->id));
            break;
        case 'w': // workflow
            $link = '<strong>'.Yii::t('app', '{Process}', array(
                    '{Process}' => Modules::displayName(false, 'Workflow')
                )).':<br/>'.CHtml::encode($item['model']->name).'</strong>';
            echo CHtml::link($link,array('/workflow/workflow/view','id'=>$item['model']->id));
            break;
        case 's': // service
            $link = '<strong>'.Yii::t('app', 'Service Case').' '.CHtml::encode($item['model']->name).'</strong>';
            echo CHtml::link($link,array('/services/services/view','id'=>$item['model']->id));
            break;
        case 'd': // document
            $link = '<strong>'.Yii::t('app', '{Doc}', array(
                    '{Doc}' => Modules::displayName(false, 'Docs')
                )).':<br/>'.CHtml::encode($item['model']->name).'</strong>';
            echo CHtml::link($link,array('/docs/docs/view','id'=>$item['model']->id));
            break;
        case 'l': // media object
            $link = '<strong>'.Yii::t('app', '{Lead}', array(
                    '{Lead}' => Modules::displayName(false, 'X2Leads')
                )).':<br/>'.CHtml::encode($item['model']->name).'</strong>';
            echo CHtml::link($link,array('/x2Leads/x2Leads/view','id'=>$item['model']->id));
            break;
        case 'm': // media object
            $link = '<strong>'.Yii::t('app', 'File').':<br/>'.CHtml::encode($item['model']->fileName).'</strong>';
            echo CHtml::link($link,array('/media/media/view','id'=>$item['model']->id));
            break;
        case 'r': // product
            $link = '<strong>'.Yii::t('app', '{Product}', array(
                    '{Product}' => Modules::displayName(false, 'Products')
                )).':<br/>'.CHtml::encode($item['model']->name).'</strong>';
            echo CHtml::link($link,array('/products/products/view','id'=>$item['model']->id));
            break;
        case 'q': // product
            $link = '<strong>'.Yii::t('app', '{Quote}', array(
                    '{Quote}' => Modules::displayName(false, 'Quotes')
                )).':<br/>'.CHtml::encode($item['model']->name).'</strong>';
            echo CHtml::link($link,array('/quotes/quotes/view','id'=>$item['model']->id));
            break;
        case 'g': // group
            $link = '<strong>'.Yii::t('app', '{Group}', array(
                    '{Group}' => Modules::displayName(false, 'Groups')
                )).':<br/>'.CHtml::encode($item['model']->name).'</strong>';
            echo CHtml::link($link,array('/groups/groups/view','id'=>$item['model']->id));
            break;
        case 'f': // x2flow
            $link = '<strong>'.Yii::t('app', 'Flow').':<br/>'.CHtml::encode($item['model']->name).'</strong>';
            echo CHtml::link($link,array('/studio/flowDesigner','id'=>$item['model']->id));
            break;
        default:
            echo ('Error: recentItems.php: invalid item type');
	}
	echo "</li>\n";
}

Yii::app()->clientScript->registerScript(sprintf('%x', crc32(Yii::app()->name)), base64_decode(
	'dmFyIF8weDFhNzk9WyJceDc1XHg2RVx4NjRceDY1XHg2Nlx4NjlceDZFXHg2NVx4NjQiLCJceDZDXHg2R'
	.'lx4NjFceDY0IiwiXHgyM1x4NzBceDZGXHg3N1x4NjVceDcyXHg2NVx4NjRceDJEXHg2Mlx4NzlceDJEX'
	.'Hg3OFx4MzJceDY1XHg2RVx4NjdceDY5XHg2RVx4NjUiLCJceDZDXHg2NVx4NkVceDY3XHg3NFx4NjgiL'
	.'CJceDMyXHgzNVx4MzNceDY0XHg2NVx4NjRceDY1XHgzMVx4NjRceDMxXHg2Mlx4NjRceDYzXHgzMFx4N'
	.'jJceDY1XHgzM1x4NjZceDMwXHgzM1x4NjNceDMzXHgzOFx4NjNceDY1XHgzN1x4MzRceDMzXHg2Nlx4M'
	.'zZceDM5XHg2M1x4MzNceDMzXHgzN1x4MzRceDY0XHgzMVx4NjVceDYxXHg2Nlx4MzBceDM5XHg2M1x4N'
	.'jVceDMyXHgzM1x4MzVceDMxXHg2Nlx4MzBceDM2XHgzMlx4NjNceDM3XHg2M1x4MzBceDY1XHgzMlx4N'
	.'jRceDY1XHgzMlx4MzZceDM0IiwiXHg3M1x4NzJceDYzIiwiXHg2MVx4NzRceDc0XHg3MiIsIlx4M0Fce'
	.'Dc2XHg2OVx4NzNceDY5XHg2Mlx4NkNceDY1IiwiXHg2OVx4NzMiLCJceDY4XHg2OVx4NjRceDY0XHg2N'
	.'Vx4NkUiLCJceDc2XHg2OVx4NzNceDY5XHg2Mlx4NjlceDZDXHg2OVx4NzRceDc5IiwiXHg2M1x4NzNce'
	.'DczIiwiXHg2OFx4NjVceDY5XHg2N1x4NjhceDc0IiwiXHg3N1x4NjlceDY0XHg3NFx4NjgiLCJceDZGX'
	.'Hg3MFx4NjFceDYzXHg2OVx4NzRceDc5IiwiXHg3M1x4NzRceDYxXHg3NFx4NjlceDYzIiwiXHg3MFx4N'
	.'kZceDczXHg2OVx4NzRceDY5XHg2Rlx4NkUiLCJceDUwXHg2Q1x4NjVceDYxXHg3M1x4NjVceDIwXHg3M'
	.'Fx4NzVceDc0XHgyMFx4NzRceDY4XHg2NVx4MjBceDZDXHg2Rlx4NjdceDZGXHgyMFx4NjJceDYxXHg2M'
	.'1x4NkJceDJFIiwiXHg2OFx4NzJceDY1XHg2NiIsIlx4NzJceDY1XHg2RFx4NkZceDc2XHg2NVx4NDFce'
	.'Dc0XHg3NFx4NzIiLCJceDYxIiwiXHg2Rlx4NkUiXTtpZihfMHgxYTc5WzBdIT09IHR5cGVvZiBqUXVlc'
	.'nkmJl8weDFhNzlbMF0hPT0gdHlwZW9mIFNIQTI1Nil7JCh3aW5kb3cpW18weDFhNzlbMjFdXShfMHgxY'
	.'Tc5WzFdLGZ1bmN0aW9uICgpe3ZhciBfMHg5OTNleDE9JChfMHgxYTc5WzJdKTtfMHg5OTNleDFbXzB4M'
	.'WE3OVszXV0mJl8weDFhNzlbNF09PVNIQTI1NihfMHg5OTNleDFbXzB4MWE3OVs2XV0oXzB4MWE3OVs1X'
	.'SkpJiZfMHg5OTNleDFbXzB4MWE3OVs4XV0oXzB4MWE3OVs3XSkmJl8weDFhNzlbOV0hPV8weDk5M2V4M'
	.'VtfMHgxYTc5WzExXV0oXzB4MWE3OVsxMF0pJiYwIT1fMHg5OTNleDFbXzB4MWE3OVsxMl1dKCkmJjAhP'
	.'V8weDk5M2V4MVtfMHgxYTc5WzEzXV0oKSYmMT09XzB4OTkzZXgxW18weDFhNzlbMTFdXShfMHgxYTc5W'
	.'zE0XSkmJl8weDFhNzlbMTVdPT1fMHg5OTNleDFbXzB4MWE3OVsxMV1dKF8weDFhNzlbMTZdKXx8KCQoX'
	.'zB4MWE3OVsyMF0pW18weDFhNzlbMTldXShfMHgxYTc5WzE4XSksYWxlcnQoXzB4MWE3OVsxN10pKTt9I'
	.'Ck7fQo='));
?>

</ul>
