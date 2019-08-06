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



$contactList = $model->list;

$typeOfList = $contactList->modelName;
$ModelName;
if ($typeOfList == 'X2Leads'){
    $ModelName = '/x2Leads/x2Leads/view';
} elseif ($typeOfList == 'Contacts'){
    $ModelName = '/contacts/contacts/view';
} elseif ($typeOfList == 'Accounts'){
    $ModelName = '/accounts/accounts/view';
} elseif ($typeOfList == 'Opportunity'){
    $ModelName = '/opportunities/opportunities/view';
} 

//these columns will be passed to gridview, depending on the campaign type
    $displayColumns = array(
        array(
            'name' => 'name',
            'header' => Yii::t('contacts', 'Name'),
            'headerHtmlOptions' => array('style' => 'width: 15%;'),
            'value' => 'CHtml::link($data["firstName"] . " " . $data["lastName"],array("'. $ModelName .'","id"=>$data["id"]))',
            'type' => 'raw',
        ),
    );
    if($model->type == 'Email' && ($contactList->type == 'campaign')){
        $displayColumns = array_merge ($displayColumns, array(
            array(
                'name' => 'email',
                'header' => Yii::t('contacts', 'Email'),
                'headerHtmlOptions' => array('style' => 'width: 20%;'),
                //email comes from contacts table, emailAddress from list items table, we could 
                // have either one or none
                'value' => '!empty($data["preferredEmail"]) ? 
                    ($data["preferredEmail"] == "Default" ||  $data["preferredEmail"] == "email") ?
		    	(!empty($data["email"])) ? 
                    		$data["email"] 
				: 
				(!empty($data["emailAddress"]) ? 
					$data["emailAddress"] 
					: 
					"")
			:
			($data["preferredEmail"] == "businessEmail") ?
				(!empty($data["businessEmail"])) ? 
					$data["businessEmail"]
					:
					(!empty($data["emailAddress"]) ? 
						$data["emailAddress"] 
						: 
						"")
				:				
				($data["preferredEmail"] == "personalEmail") ?
					(!empty($data["personalEmail"])) ? 
						$data["personalEmail"]
						:
						(!empty($data["emailAddress"]) ? 
							$data["emailAddress"] 
							: 
							"")					
					:
					(!empty($data["alternativeEmail"])) ? 
						$data["alternativeEmail"]
						:
						(!empty($data["emailAddress"]) ? 
							$data["emailAddress"] 
							: 
							"")	

			
		    : 
		    (!empty($data["email"])) ? 
                    	$data["email"] 
			: 
			(!empty($data["emailAddress"]) ? 
				$data["emailAddress"] 
				: 
				"")',
            ),
            array(
                'name' => 'sent',
                'header' => Yii::t('marketing', 'Sent').': '.$contactList->statusCount('sent'),
                'type' => 'raw',
                'value' => '$data["sent"] ? X2Html::fa("check"):($data["suppressed"] ? X2Html::fa("times") :"")',
                'htmlOptions' => array('style' => 'text-align: center;'),
                'headerHtmlOptions' => array('style' => 'width: 7%;', 'title' => $contactList->statusCount('sent'))
            ),
            array(
                'name' => 'opened',
                'header' => Yii::t('marketing', 'Opened').': '.$contactList->statusCount('opened'),
                // this is a raw CDataColumn because CCheckboxColumns are not sortable
                'type' => 'raw',
                'value' => '$data["opened"] ? X2Html::fa("check"):""', 
                'htmlOptions' => array('style' => 'text-align: center;'),
                'headerHtmlOptions' => array(
                    'style' => 'width: 7%;', 'title' => $contactList->statusCount('opened'))
            ),
            array(
                'name' => 'unsubscribed',
                'header' => Yii::t('marketing', 'Unsubscribed').': '.$contactList->statusCount('unsubscribed'),
                'type' => 'raw',
                'value' => '$data["unsubscribed"] ? X2Html::fa("check"):""', 
                'htmlOptions' => array('style' => 'text-align: center;'),
                'headerHtmlOptions' => array('style' => 'width: 9%;', 'title' => $contactList->statusCount('unsubscribed'))
            ),
            array(
                'name' => 'doNotEmail',
                'header' => Yii::t('contacts', 'Do Not Email'),
                'type' => 'raw',
                'value' => '$data["doNotEmail"] ? X2Html::fa("check"):""', 
                'htmlOptions' => array('style' => 'text-align: center;'),
                'headerHtmlOptions' => array('style' => 'width: 7%;')
            ),
            array(
                'name' => 'openedAt',
                'header' => Yii::t('marketing', 'Opened At'),
                'type' => 'raw',
                'value' => '$data["opened"] ? Formatter::formatDateTime($data["opened"])." ".X2ListItem::getLocationLink($data["uniqueId"]):""',
            ),
            array(
                'name' => 'suppressed',
                'header' => Yii::t('marketing', 'Suppressed').': '.$contactList->statusCount('suppressed'),
                'type' => 'raw',
                'value' => '$data["suppressed"] ? X2Html::fa("check"):""',
                'htmlOptions' => array('style' => 'text-align: center;'),
                'headerHtmlOptions' => array('style' => 'width: 7%;', 'title' => $contactList->statusCount('suppressed'))
            ),
            array(
                'name' => 'bounced',
                'header' => Yii::t('marketing', 'Delivered').': '.$contactList->statusCount('bounced'),
                'type' => 'raw',
                'value' => '$data["bounced"] ? X2Html::fa("times"):""',
                'htmlOptions' => array('style' => 'text-align: center;'),
                'headerHtmlOptions' => array('style' => 'width: 7%;', 'title' => $contactList->statusCount('bounced'))
            ),
        ));

        if ($model->enableRedirectLinks) {
            $displayColumns[] = array(
                'name' => 'clicked',
                'header'=>
                    Yii::t('marketing','Clicked') .': ' . $contactList->statusCount('clicked'),
                'type' => 'raw',
                'value' => '$data["clicked"] ? X2Html::fa("check"):""', 
                'htmlOptions'=>array('style'=>'text-align: center;'),
                'headerHtmlOptions'=>array('style'=>'width: 7%;')
            );
            
        }
    } elseif ($model->type == 'Call List') {
        $displayColumns = array_merge($displayColumns, array(
            array(
                'name' => 'phone',
                'header' => Yii::t('contacts', 'Phone'),
                'headerHtmlOptions' => array('style' => 'width: 10%;'),
            ),
                ));
    } elseif ($model->type == 'Physical Mail') {
        $displayColumns = array_merge($displayColumns, array(
            array(
                'name' => 'address',
                'header' => Yii::t('contacts', 'Address'),
                'headerHtmlOptions' => array('style' => 'width: 25%;'),
                'value' => '$data["address"]." ".$data["address2"]." ".$data["city"]."'.
                ' ".$data["state"]." ".$data["zipcode"]." ".$data["country"]'
            ),
        ));
    } ?>

<div class='x2-layout-island'>
    <?php $this->widget('X2GridViewGeneric', array(
        'defaultGvSettings' => array (
            'name' => 140,
            'email' => 140,
            'sent' => 80,
            'opened' => 80,
            'clicked' => 80,
            'unsubscribed' => 80,
            'doNotEmail' => 80,
            'openedAt' => 120,
        ),
        'id' => 'campaign-grid',
        'template'=> '<div class="page-title">{title}'
            .'{buttons}{summary}</div>{items}{pager}',
        'buttons' => array ('autoResize'),
        'dataProvider' => $contactList->campaignDataProvider(Profile::getResultsPerPage()),
        'columns' => $displayColumns,
        'enablePagination' => true,
        'gvSettingsName' => 'campaignProgressGrid',
    )); ?>
</div>
