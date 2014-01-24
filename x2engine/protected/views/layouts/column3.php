<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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

$this->beginContent('//layouts/main');
//$themeURL = Yii::app()->theme->getBaseUrl();

Yii::app()->clientScript->registerScript('logos',base64_decode(
    'JCh3aW5kb3cpLmxvYWQoZnVuY3Rpb24oKXt2YXIgYT0kKCIjcG93ZXJlZC1ieS14MmVuZ2luZSIpO2lmKCFhLmxlb'
    .'md0aHx8YS5hdHRyKCJzcmMiKSE9eWlpLmJhc2VVcmwrIi9pbWFnZXMvcG93ZXJlZF9ieV94MmVuZ2luZS5wbmciK'
    .'XskKCJhIikucmVtb3ZlQXR0cigiaHJlZiIpO2FsZXJ0KCJQbGVhc2UgcHV0IHRoZSBsb2dvIGJhY2siKX19KTs='));
?>

<!--<div id="sidebar-left">-->
    <!-- sidebar -->
    <div id='sidebar-left-widget-box'>
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
            ($this->id=='profile' && $this->action->id=='view' && 
             (!(isset ($_GET['publicProfile']) && $_GET['publicProfile']) && 
              $_GET['id'] == Yii::app()->params->profile->id)))) {

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
