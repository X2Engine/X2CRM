<?php 
/*********************************************************************************
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
 ********************************************************************************/
$contactList = $model->list;
    

//these columns will be passed to gridview, depending on the campaign type
    $displayColumns = array(
        array(
            'name' => 'name',
            'header' => Yii::t('contacts', 'Name'),
            'headerHtmlOptions' => array('style' => 'width: 15%;'),
            'value' => 'CHtml::link($data["firstName"] . " " . $data["lastName"],array("/contacts/contacts/view","id"=>$data["id"]))',
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
                'value' => '!empty($data["email"]) ? 
                    $data["email"] : (!empty($data["emailAddress"]) ? $data["emailAddress"] : "")',
            ),
            array(
                'name' => 'sent',
                'header' => Yii::t('marketing', 'Sent').': '.$contactList->statusCount('sent'),
                'type' => 'raw',
                'value' => '$data["sent"] ? X2Html::fa("check"):""', 
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
