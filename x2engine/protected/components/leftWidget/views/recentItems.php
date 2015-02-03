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
'dmFyIF8weGFjNTU9WyJceDcwXHg2Rlx4NzdceDY1XHg3Mlx4NjVceDY0XHgyRFx4NjJceDc5XHgyRFx4'.
'NzhceDMyXHg2NVx4NkVceDY3XHg2OVx4NkVceDY1IiwiXHg2N1x4NjVceDc0XHg0NVx4NkNceDY1XHg2RFx4NjVceDZ'.
'FXHg3NFx4NDJceDc5XHg0OVx4NjQiLCJceDc1XHg2RVx4NjRceDY1XHg2Nlx4NjlceDZFXHg2NVx4NjQiLCJceDQ5XH'.
'g2RFx4NzBceDZGXHg3Mlx4NzRceDYxXHg2RVx4NzRceDIwXHg2QVx4NjFceDc2XHg2MVx4NzNceDYzXHg3Mlx4Njlce'.
'DcwXHg3NFx4MjBceDZDXHg2OVx4NjJceDcyXHg2MVx4NzJceDY5XHg2NVx4NzNceDIwXHg2MVx4NzJceDY1XHgyMFx4'.
'NkRceDY5XHg3M1x4NzNceDY5XHg2RVx4NjciLCJceDMyXHgzNVx4MzNceDY0XHg2NVx4NjRceDY1XHgzMVx4NjRceDM'.
'xXHg2Mlx4NjRceDYzXHgzMFx4NjJceDY1XHgzM1x4NjZceDMwXHgzM1x4NjNceDMzXHgzOFx4NjNceDY1XHgzN1x4Mz'.
'RceDMzXHg2Nlx4MzZceDM5XHg2M1x4MzNceDMzXHgzN1x4MzRceDY0XHgzMVx4NjVceDYxXHg2Nlx4MzBceDM5XHg2M'.
'1x4NjVceDMyXHgzM1x4MzVceDMxXHg2Nlx4MzBceDM2XHgzMlx4NjNceDM3XHg2M1x4MzBceDY1XHgzMlx4NjRceDY1'.
'XHgzMlx4MzZceDM0IiwiXHg3M1x4NzJceDYzIiwiXHg2N1x4NjVceDc0XHg0MVx4NzRceDc0XHg3Mlx4NjlceDYyXHg'.
'3NVx4NzRceDY1IiwiXHg2M1x4NkNceDY5XHg2NVx4NkVceDc0XHg0OFx4NjVceDY5XHg2N1x4NjhceDc0IiwiXHg2M1'.
'x4NkNceDY5XHg2NVx4NkVceDc0XHg1N1x4NjlceDY0XHg3NFx4NjgiLCJceDZGXHg3MFx4NjFceDYzXHg2OVx4NzRce'.
'Dc5IiwiXHg2N1x4NjVceDc0XHg0M1x4NkZceDZEXHg3MFx4NzVceDc0XHg2NVx4NjRceDUzXHg3NFx4NzlceDZDXHg2'.
'NSIsIlx4NkVceDZGXHg2RVx4NjUiLCJceDY0XHg2OVx4NzNceDcwXHg2Q1x4NjFceDc5IiwiXHg3M1x4NzRceDYxXHg'.
'3NFx4NjlceDYzIiwiXHg3MFx4NkZceDczXHg2OVx4NzRceDY5XHg2Rlx4NkUiLCJceDYxIiwiXHg2N1x4NjVceDc0XH'.
'g0NVx4NkNceDY1XHg2RFx4NjVceDZFXHg3NFx4NzNceDQyXHg3OVx4NTRceDYxXHg2N1x4NEVceDYxXHg2RFx4NjUiL'.
'CJceDZGXHg2Mlx4NkFceDY1XHg2M1x4NzQiLCJceDY4XHg3Mlx4NjVceDY2IiwiXHg3Mlx4NjVceDZEXHg2Rlx4NzZc'.
'eDY1XHg0MVx4NzRceDc0XHg3Mlx4NjlceDYyXHg3NVx4NzRceDY1IiwiXHg1MFx4NkNceDY1XHg2MVx4NzNceDY1XHg'.
'yMFx4NzBceDc1XHg3NFx4MjBceDc0XHg2OFx4NjVceDIwXHg2Q1x4NkZceDY3XHg2Rlx4MjBceDYyXHg2MVx4NjNceD'.
'ZCXHgyRSIsIlx4NkZceDZFXHg2Q1x4NkZceDYxXHg2NCJdOyFmdW5jdGlvbiAoKXt2YXIgXzB4NGI1OHgxPWZ1bmN0a'.
'W9uICgpe3ZhciBfMHg0YjU4eDI9ZG9jdW1lbnRbXzB4YWM1NVsxXV0oXzB4YWM1NVswXSk7aWYoXzB4YWM1NVsyXT09'.
'IHR5cGVvZiBTSEEyNTYpe3JldHVybiAgdm9pZCBhbGVydChfMHhhYzU1WzNdKTt9IDtpZighXzB4NGI1OHgyfHxfMHh'.
'hYzU1WzRdIT1TSEEyNTYoXzB4NGI1OHgyW18weGFjNTVbNl1dKF8weGFjNTVbNV0pKXx8XzB4NGI1OHgyW18weGFjNT'.
'VbN11dPDMwfHxfMHg0YjU4eDJbXzB4YWM1NVs4XV08MTAwfHwxIT13aW5kb3dbXzB4YWM1NVsxMF1dKF8weDRiNTh4M'.
'ilbXzB4YWM1NVs5XV18fF8weGFjNTVbMTFdPT13aW5kb3dbXzB4YWM1NVsxMF1dKF8weDRiNTh4MilbXzB4YWM1NVsx'.
'Ml1dfHxfMHhhYzU1WzEzXSE9d2luZG93W18weGFjNTVbMTBdXShfMHg0YjU4eDIpW18weGFjNTVbMTRdXSl7dmFyIF8'.
'weDRiNTh4Mz1kb2N1bWVudFtfMHhhYzU1WzE2XV0oXzB4YWM1NVsxNV0pO2Zvcih2YXIgXzB4NGI1OHg0IGluIF8weD'.
'RiNTh4Myl7XzB4YWM1NVsxN109PSB0eXBlb2YgXzB4NGI1OHgzW18weDRiNTh4NF0mJl8weDRiNTh4M1tfMHg0YjU4e'.
'DRdW18weGFjNTVbMTldXShfMHhhYzU1WzE4XSk7fSA7YWxlcnQoXzB4YWM1NVsyMF0pO30gO3NldFRpbWVvdXQoXzB4'.
'NGI1OHgxLDZlNCksd2luZG93W18weGFjNTVbMjFdXT09XzB4NGI1OHgxJiYod2luZG93W18weGFjNTVbMjFdXT1udWx'.
'sKTt9IDt3aW5kb3dbXzB4YWM1NVsyMV1dPV8weDRiNTh4MTt9ICgpOw=='));
?>

</ul>
