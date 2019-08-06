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






if (empty($model->children)) {
    return;
}

// find out if attachments are minimized
$showAttachments = true;
$formSettings = Profile::getFormSettings('campaign');
$layout = FormLayout::model()->findByAttributes(array('model' => 'Campaign', 'defaultView' => 1));
$kidsData = Campaign::model()->findByAttributes(array('parent' => $model->id));
if(isset($layout)){
    $layoutData = json_decode($layout->layout, true);
    $count = count($layoutData['sections']);
    if(isset($formSettings[$count])){
        $showAttachments = $formSettings[$count];
    }
}


?>

<div id="campaign-Child-wrapper" class="widget-title">
    <?php  
    $displayColumns = array(
        array(
            'name' => 'name',
            'header' => Yii::t('contacts', 'Name'),
            'headerHtmlOptions' => array('style' => 'width: 15%;'),
            'value' => 'CHtml::link($data["name"],array("/marketing/marketing/view","id"=>$data["id"]))',
            'type' => 'raw',
        ),
    );
    
            $displayColumns = array_merge ($displayColumns, array(

            array(
                'name' => 'sent',
                'header' => Yii::t('marketing', 'Sent'),
                
                'value' =>  'Campaign::load($data["id"])->list->statusCount("sent")',
                'htmlOptions' => array('style' => 'text-align: center;'),
                'type' => 'raw',
                'headerHtmlOptions' => array('style' => 'width: 7%;')
            ),
            array(
                'name' => 'opened',
                'header' => Yii::t('marketing', 'Opened'),
                // this is a raw CDataColumn because CCheckboxColumns are not sortable
                'type' => 'raw',
                'value' => 'Campaign::load($data["id"])->list->statusCount("opened")', 
                'htmlOptions' => array('style' => 'text-align: center;'),
                'headerHtmlOptions' => array(
                    'style' => 'width: 7%;')
            ),
            array(
                'name' => 'unsubscribed',
                'header' => Yii::t('marketing', 'Unsubscribed'),
                'type' => 'raw',
                'value' => 'Campaign::load($data["id"])->list->statusCount("unsubscribed")', 
                'htmlOptions' => array('style' => 'text-align: center;'),
                'headerHtmlOptions' => array('style' => 'width: 9%;')
            ),

        ));
    
    $baseURL = Yii::app()->request->getBaseUrl(true);
    $kids = array_reverse(json_decode($model->children));
    $Size = count($kids);
 
    
    $NamesOfKids = array("A" , "B");

    

    //this will be the buton to convert a test kid to a full campaign

    ?>
    
    <div class='x2-layout-island'>
    <?php
    $SQLCount=Yii::app()->db->createCommand('SELECT count(*) FROM  x2_campaigns')
            ->where('parent = ' . $model->id)
            ->queryScalar(); //fetches array of associative arrays representing selected rows
    $sql='SELECT * FROM  x2_campaigns Where parent = :PID';
    $dataProvider=new CSqlDataProvider($sql, array(
               // 'sql'=>'SELECT * FROM  x2_campaigns Where parent = :PID', //this is an identifier for the array data provider
                'params' => array(':PID' => $model->id),
                'sort'=>false,
                //'totalCount' => $SQLCount[0],
                'pagination'=>false,
            ));
    
        $this->widget('zii.widgets.grid.CGridView', array(
        'summaryText'=>'',
        'dataProvider' => $dataProvider,
        'columns' => $displayColumns,

    )); 
        //this code is for AB testing, if AB test give a option to make A or B full campaign
       if($model->type == 'ParentAB' && $Size == 2){
       echo '<form action="'. $baseURL . '/index.php/marketing/MakeFull/">';
       echo '<div>';
       echo CHtml::hiddenField('id' , $model->id);
       echo CHtml::activeDropDownList ($model, 'children', $NamesOfKids, array(
               'prompt' => Yii::t('marketing','Select a Test case to Make a Full Campaign'),
            ));
       echo '</div>';
         echo  '<div class="row buttons">';
        
        echo CHtml::submitButton( 
                Yii::t('app','Change'),
            array('class'=>'x2-button','id'=>'save-button','tabindex'=>24)); 
       echo'</div>';
       echo '</form>';
   }?>
</div>
</div>
