<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/
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
            $link = '<strong>'.$item['model']->name.'</strong><br />'.$item['model']->phone;
            echo CHtml::link($link,array('/contacts/contacts/view','id'=>$item['model']->id));
            break;
        case 'a': // account
            $link = '<strong>'.Yii::t('app', 'Account').':<br/>'.$item['model']->name.'</strong><br />'.
                $item['model']->phone;
            echo CHtml::link($link,array('/accounts/accounts/view','id'=>$item['model']->id));
            break;
        case 'p': // campaign
            $link = '<strong>'.Yii::t('app', 'Campaign').':<br/>'.$item['model']->name.'</strong>';
            echo CHtml::link($link,array('/marketing/marketing/view','id'=>$item['model']->id));
            break;
        case 'o': // opportunity
            $link = '<strong>'.Yii::t('app', 'Opportunity').':<br/>'.$item['model']->name.'</strong>';
            echo CHtml::link($link,array('/opportunities/opportunities/view','id'=>$item['model']->id));
            break;
        case 'w': // workflow
            $link = '<strong>'.Yii::t('app', 'Workflow').':<br/>'.$item['model']->name.'</strong>';
            echo CHtml::link($link,array('/workflow/workflow/view','id'=>$item['model']->id));
            break;
        case 's': // service
            $link = '<strong>'.Yii::t('app', 'Service Case').' '.$item['model']->name.'</strong>';
            echo CHtml::link($link,array('/services/services/view','id'=>$item['model']->id));
            break;
        case 'd': // document
            $link = '<strong>'.Yii::t('app', 'Doc').':<br/>'.$item['model']->name.'</strong>';
            echo CHtml::link($link,array('/docs/docs/view','id'=>$item['model']->id));
            break;
        case 'm': // media object
            $link = '<strong>'.Yii::t('app', 'File').':<br/>'.$item['model']->fileName.'</strong>';
            echo CHtml::link($link,array('/media/media/view','id'=>$item['model']->id));
            break;
        case 'r': // product
            $link = '<strong>'.Yii::t('app', 'Product').':<br/>'.$item['model']->name.'</strong>';
            echo CHtml::link($link,array('/products/products/view','id'=>$item['model']->id));
            break;
        case 'q': // product
            $link = '<strong>'.Yii::t('app', 'Quote').':<br/>'.$item['model']->name.'</strong>';
            echo CHtml::link($link,array('/quotes/quotes/view','id'=>$item['model']->id));
            break;
        case 'g': // group
            $link = '<strong>'.Yii::t('app', 'Group').':<br/>'.$item['model']->name.'</strong>';
            echo CHtml::link($link,array('/groups/groups/view','id'=>$item['model']->id));
            break;
        default:
            echo ('Error: recentItems.php: invalid item type');
	}
	echo "</li>\n";
}
?>
</ul>

