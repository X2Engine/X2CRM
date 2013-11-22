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


$this->beginContent('//layouts/main');
//$themeURL = Yii::app()->theme->getBaseUrl();

Yii::app()->clientScript->registerScript('logos',base64_decode(
    'JCh3aW5kb3cpLmxvYWQoZnVuY3Rpb24oKXt2YXIgYT0kKCIjcG93ZXJlZC1ieS14MmVuZ2luZSIpO2lmKCFhLmxlb'
    .'md0aHx8YS5hdHRyKCJzcmMiKSE9eWlpLmJhc2VVcmwrIi9pbWFnZXMvcG93ZXJlZF9ieV94MmVuZ2luZS5wbmciK'
    .'XskKCJhIikucmVtb3ZlQXR0cigiaHJlZiIpO2FsZXJ0KCJQbGVhc2UgcHV0IHRoZSBsb2dvIGJhY2siKX19KTs='));

?>

<!--<div id="sidebar-left">-->
    <!-- sidebar -->
    <?php

        $echoedFirstSideBarLeft = false;
        // Echoes sidebar left container div if it hasn't already been echoed.
        if(!function_exists('echoFirstSideBarLeft')){
            function echoFirstSideBarLeft (&$echoedFirstSideBarLeft) {
                if (!$echoedFirstSideBarLeft) {
                    echo '<div class="sidebar-left">';
                    $echoedFirstSideBarLeft = true;
                }
            }
        }

        if(isset($this->actionMenu) && !empty($this->actionMenu)) {
            echoFirstSideBarLeft ($echoedFirstSideBarLeft);
            $this->beginWidget('zii.widgets.CPortlet',array(
                'title'=>Yii::t('app','Actions'),
                'id'=>'actions'
            ));

            $this->widget(
                'zii.widgets.CMenu',array('items'=>$this->actionMenu,'encodeLabel'=>false));
            $this->endWidget();
        }

        foreach($this->leftPortlets as &$portlet) {
            echoFirstSideBarLeft ($echoedFirstSideBarLeft);
            $this->beginWidget('zii.widgets.CPortlet',$portlet['options']);
            echo $portlet['content'];
            $this->endWidget();
        }

        if(isset($this->modelClass) &&
           ($this->modelClass == 'Services' || $this->modelClass == 'Actions' ||
            $this->modelClass == 'BugReports' || $this->modelClass == 'X2Calendar' ||
            ($this->id=='site' && $this->action->id=='whatsNew'))) {
            echoFirstSideBarLeft ($echoedFirstSideBarLeft);
            $this->renderPartial ('_sidebarLeftExtraContent');
        }

        if ($echoedFirstSideBarLeft) echo "</div>";

        echo "<div class='sidebar-left'>";
        $this->widget('TopContacts',array(
            'id'=>'top-contacts',
            'widgetName' => 'TopContacts',
            'widgetLabel' => 'Top Contacts'
        ));
        echo "</div><div class='sidebar-left'>";
        $this->widget('RecentItems',array(
            'id'=>'recent-items',
            'widgetName' => 'RecentItems',
            'widgetLabel' => 'Recent Items'
        ));

        // collapse or expand left widget and save setting to user profile
        Yii::app()->clientScript->registerScript('leftWidgets','
            $(".left-widget-min-max").click(function(e){
                e.preventDefault();
                var link=this;
                var action = $(this).attr ("value");
                $.ajax({
                    url:"'.Yii::app()->request->getScriptUrl ().'/site/minMaxLeftWidget'.'",
                    data:{
                        action: action,
                        widgetName: $(link).attr ("name")
                    },
                    success:function(data){
                        if (data === "failure") return;
                        if(action === "expand"){
                            $(link).html("<img src=\'"+yii.themeBaseUrl+"/images/icons/'.
                                'Collapse_Widget.png\' />");
                            $(link).parents(".portlet-decoration").next().slideDown();
                            $(link).attr ("value", "collapse");
                        }else if(action === "collapse"){
                            $(link).html("<img src=\'"+yii.themeBaseUrl+"/images/icons/'.
                                'Expand_Widget.png\' />");
                            $(link).parents(".portlet-decoration").next().slideUp();
                            $(link).attr ("value", "expand");
                        }
                    }
                });
            });
        ');
        ?>
        </div>
<!--</div>-->
<!--</div>-->
<div id="flexible-content">
    <div id="sidebar-right">
        <?php
        $this->widget('SortableWidgets', array(
            //list of items
            'portlets'=>$this->portlets,
            'jQueryOptions'=>array(
                'opacity'=>0.6,    //set the dragged object's opacity to 0.6
                'handle'=>'.portlet-decoration',    //specify tag to be used as handle
                'distance'=>20,
                'delay'=>150,
                'revert'=>50,
                'update'=>"js:function(){
                    $.ajax({
                            type: 'POST',
                            url: '{$this->createUrl('/site/widgetOrder')}',
                            data: $(this).sortable('serialize')
                    });
                }"
            )
        ));
        ?>
    </div>
    <div id="content-container">
        <div id="content">
            <!-- content -->
            <?php echo $content; ?>
        </div>
    </div>
</div>
<?php $this->endContent();
