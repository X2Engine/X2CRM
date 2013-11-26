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


Yii::import('zii.widgets.jui.CJuiWidget');

/**
 * CJuiSortable class.
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @package X2CRM.components
 */
class SortableWidgets extends CJuiWidget {

    /**
     * @var array list of sortable items (id=>item content).
     * Note that the item contents will not be HTML-encoded.
     */
    public $portlets = array();
    public $jQueryOptions = array();

    /**
     * @var string the name of the container element that contains all items. Defaults to 'ul'.
     */
    public $tagName = 'div';

    /**
     * Run this widget.
     * This method registers necessary javascript and renders the needed HTML code.
     */
    public function run(){
        $themeURL = Yii::app()->theme->getBaseUrl();
        Yii::app()->clientScript->registerScript('logos', base64_decode(
                        'JCh3aW5kb3cpLmxvYWQoZnVuY3Rpb24oKXt2YXIgYT0kKCIjcG93ZXJlZC1ieS14MmVuZ2luZSIpO2lmKCFhLmxlb'
                        .'md0aHx8YS5hdHRyKCJzcmMiKSE9eWlpLmJhc2VVcmwrIi9pbWFnZXMvcG93ZXJlZF9ieV94MmVuZ2luZS5wbmciK'
                        .'XskKCJhIikucmVtb3ZlQXR0cigiaHJlZiIpO2FsZXJ0KCJQbGVhc2UgcHV0IHRoZSBsb2dvIGJhY2siKX19KTs='));

        Yii::app()->clientScript->registerScript('toggleWidgetState', "
            function toggleWidgetState(widget,state) {
                if($('#widget_' + widget).hasClass('ui-sortable-helper') == false) {
                    $.ajax({
                        url: '".CHtml::normalizeUrl(array('/site/widgetState'))."',
                        type: 'GET',
                        data: 'widget='+widget+'&state='+state,
                        success: function(response) {
                            if(response === 'success') {
                                var link = $('#widget_'+widget+' .portlet-minimize a.portlet-minimize-button');
                                var newLink = ($(link).find('img').attr('class')=='expand-widget') ? '<img src=\"".$themeURL."/images/icons/Collapse_Widget.png\" class=\'collapse-widget\' />' : '<img src=\"".$themeURL."/images/icons/Expand_Widget.png\" class=\'expand-widget\'/>';            // toggle link between [+] and [-]
                                link.html(newLink);

                                // slide widget open or closed
                                $('#widget_'+widget+' .portlet-content').toggle({
                                    effect: 'blind',
                                    duration: 200,
                                    complete: function() {
                                        // for google maps, trigger a resize event
                                        if(widget === 'GoogleMaps' && $(this).is(':visible')) {
                                            if (!x2.googleMapsWidget.instantiated) {
                                                runGoogleMapsWidget ();
                                            } else {
                                                google.maps.event.trigger(window.map,'resize');
                                            }
                                        }
                                    }
                                });
                            }
                        }
                    });
                }

            }
        ", CClientScript::POS_HEAD);

        $id = $this->getId(); //get generated id
        if(isset($this->htmlOptions['id']))
            $id = $this->htmlOptions['id'];
        else
            $this->htmlOptions['id'] = $id;

        $options = empty($this->jQueryOptions) ? '' : CJavaScript::encode($this->jQueryOptions);
        Yii::app()->getClientScript()->registerScript('SortableWidgets'.'#'.$id, "jQuery('#{$id}').sortable({$options});");

        echo CHtml::openTag($this->tagName, $this->htmlOptions)."\n";

        $widgetHideList = array();
        if(!Yii::app()->user->isGuest){
            $layout = Yii::app()->params->profile->getLayout();
        }else{
            $layout = array();
        }
        $profile = yii::app()->params->profile;
        foreach($this->portlets as $class => $properties){
            if(!in_array($class, array_keys($layout['hiddenRight']))){ // show widget if it isn't hidden
                $visible = ($properties['visibility'] == '1');

                if(!$visible)
                    $widgetHideList[] = '#widget_'.$class;

                // $minimizeLink = '<div class="collapse-widget '.($visible? 'collapse-widget' : 'expand-widget').'"></div><div class="close-widget"></div>';



                $minimizeLink = CHtml::link(
                                $visible ? CHtml::image($themeURL.'/images/icons/Collapse_Widget.png', '', array('class' => 'collapse-widget')) : CHtml::image($themeURL.'/images/icons/Expand_Widget.png', '', array('class' => 'expand-widget'))
                                , '#', array('class' => 'portlet-minimize-button')
                        )
                        .' '.CHtml::link(CHtml::image($themeURL.'/images/icons/Close_Widget.png'), '#', array('onclick' => "$('#widget_$class').hideWidgetRight(); return false;"));

                // $t0 = microtime(true);
                // for($i=0;$i<100;$i++)
                $widget = $this->widget($class, $properties['params'], true);

                // $t1 = microtime(true);

                if($profile->activityFeedOrder){
                    ?>
                    <script>
                        $("#topDown").addClass('selected');
                    </script>
                    <?php
                    $activityFeedOrderSelect = 'top';
                }else{
                    ?>
                    <script>
                        $("#bottomUp").addClass('selected');
                    </script>
                    <?php
                    $activityFeedOrderSelect = 'bottom';
                }
                if($profile->mediaWidgetDrive){
                    ?>
                    <script>
                        $("#drive-selector").addClass('selected');
                    </script>
                    <?php
                }else{
                    ?>
                    <script>
                        $("#media-selector").addClass('selected');
                    </script>
                    <?php
                }
                $preferences;
                $activityFeedWidgetBgColor = '';
                if($profile != null){
                    $preferences = $profile->theme;
                    $activityFeedWidgetBgColor = $preferences['activityFeedWidgetBgColor']; // replace after new behavior added to profile model
                }
                if(!empty($widget)){
                    if($class == "ChatBox"){
                        $header = CHtml::link(Yii::t('app', 'Activity Feed'), array('/site/whatsNew'), array('style' => 'text-decoration: none; margin-right:30px;')).'
                                                    <script>
                                                        $(\'#widget-dropdown a\').css("text-align", "none");
                                                        $(\'#widget-dropdown a\').css("text-align", "center !important");
                                                    </script>
                                                <span id="gear-img-container" style="float:left">
                                                    <img src="'.Yii::app()->theme->baseUrl.'/images/widgets.png" style="opacity:0.3"
                                                                onmouseout="this.style.opacity=0.3;"
                                                                onmouseover="this.style.opacity=1" />
                                                </span>
                                                <ul class="closed" id="feed-widget-gear-menu">
                                                    <div style="text-align: left">'.Yii::t('app','Activity Feed Order').'</div>
                                                    <hr>
                                                    <div id="topDown" style="font-weight:normal; float: left; margin-right: 3px;">'.Yii::t('app','Top Down').'</div>
                                                    <div id="bottomUp" style="font-weight:normal; float: left">'.Yii::t('app','Bottom Up').'</div>
                                                    <hr>
                                                    <div style="text-align: left">'.Yii::t('app','Background Color').'</div>
                                                    <colorPicker style="padding: 0px !important;">'.
                                CHtml::textField('widgets-activity-feed-widget-bg-color', $activityFeedWidgetBgColor).
                                '</colorPicker>
                                                    </ul> ';
                    }elseif($class == "MediaBox" && Yii::app()->params->admin->googleIntegration){
                        $auth = new GoogleAuthenticator();
                        if($auth->getAccessToken()){
                            $header = '<div style="margin-right:15%;display:inline-block;">'.Yii::t('app', 'Media').'</div>
                                                <span style="float:left">
                                                    <img src="'.Yii::app()->theme->baseUrl.'/images/widgets.png" style="opacity:0.3"
                                                                onmouseout="this.style.opacity=0.3;"
                                                                onmouseover="this.style.opacity=1" />
                                                </span>
                                                <ul class="closed" id="media-widget-gear-menu">
                                                    <div style="text-align: left">'.Yii::t('app','Media Widget Settings').'</div>
                                                    <hr>
                                                    <div id="media-selector" style="font-weight:normal; float: left; margin-right: 3px;">'.Yii::t('app','X2 Media').'</div>
                                                    <div id="drive-selector" style="font-weight:normal; float: left">'.Yii::t('app','Google Drive').'</div>
                                                    <hr>
                                                    <div style="text-align: left">'.Yii::t('app','Refresh Google Drive Cache').'</div>
                                                    <hr>
                                                    <a href="#" class="x2-button" id="drive-refresh" style="font-weight:normal; float: left">'.Yii::t('app','Refresh Files').'</a>
                                                    <hr>
                                                </ul> ';
                        }else{
                            $header = Yii::t('app', Yii::app()->params->registeredWidgets[$class]);
                        }
                    }else{
                        $header = Yii::t('app', Yii::app()->params->registeredWidgets[$class]);
                    }
                    $this->beginWidget('zii.widgets.CPortlet', array(
                        'title' => '<div id="widget-dropdown" class="dropdown">'
                        .$header.
                        '<div class="portlet-minimize" onclick="toggleWidgetState(\''.$class.'\','.($visible ? 0 : 1).'); return false;">'.$minimizeLink.'</div></div>',
                        'id' => $properties['id']
                    ));
                    echo $widget;
                    $this->endWidget();
//                    // echo ($t1-$t0);
                }else{
                    echo '<div ', CHtml::renderAttributes(array('style' => 'display;none;', 'id' => $properties['id'])), '></div>';
                }
            }
        }
        Yii::app()->clientScript->registerScript('setWidgetState', '
            $(document).ready(function() {
                $("'.implode(',', $widgetHideList).'").find(".portlet-content").hide();
            });', CClientScript::POS_HEAD);
        Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getBaseUrl().'/js/spectrumSetup.js', CClientScript::POS_END);

        echo CHtml::closeTag($this->tagName);
    }

}
?>
<style>

    #gear-img-container {
        padding: 0;
        height: 18px; 
    }

    /* 
    override spectrum color picker css 
    */
    #feed-widget-gear-menu .sp-replacer {
        padding: 0px !important;
    }
    #feed-widget-gear-menu .sp-dd {
        height: 13px !important;
    }
    #feed-widget-gear-menu .sp-preview
    {
        width:20px !important;
        height: 17px !important;
        margin-right: 5px !important;
    }

    #sidebar-right .selected {
        color:white;
        background:black;
    }
    #sidebar-right .hover {
        background:grey;
    }
    #sidebar-right hr {
        margin: 0px;
        padding: 0;
    }
    #sidebar-right colorPicker > span {
        padding: 0px 0px 0px 0px !important;
    }
    #widget_ChatBox input{
        padding: 1px 1px 1px 1px !important;
        float: none;
        border: 1px solid #aaa;
        -moz-border-radius: 3px;
        -o-border-radius: 3px;
        -webkit-border-radius: 3px;
        border-radius: 3px;
    }
</style>
<script>
    $(document).ready(function() {
        $("#topDown").hover(function(){
            if(!$(this).hasClass('selected')){
                $(this).toggleClass('hover');
            }
        });
        $("#bottomUp").hover(function(){
            if(!$(this).hasClass('selected')){
                $(this).toggleClass('hover');
            }
        });
        $("#media-selector").hover(function(){
            if(!$(this).hasClass('selected')){
                $(this).toggleClass('hover');
            }
        });
        $("#drive-selector").hover(function(){
            if(!$(this).hasClass('selected')){
                $(this).toggleClass('hover');
            }
        });
        $("#topDown").click(function(){
            if($(this).hasClass('selected')) return;
            else {
                $.ajax({url:yii.baseUrl+"/index.php/site/activityFeedOrder"});
                yii.profile['activityFeedOrder']=1;
                $(this).addClass('selected');
                $(this).removeClass('hover');
                var feedbox = $('#feed-box');
                feedbox.children().each(function(i,child){feedbox.prepend(child)});
                feedbox.prop('scrollTop',0);
                $("#bottomUp").removeClass('selected');
            }
        });
        $("#bottomUp").click(function(){
            if($(this).hasClass('selected')) return;
            else {
                $.ajax({url:yii.baseUrl+"/index.php/site/activityFeedOrder"});
                yii.profile['activityFeedOrder']=0;
                $(this).addClass('selected');
                $(this).removeClass('hover');
                var feedbox = $('#feed-box');
                var scroll=feedbox.prop('scrollHeight');
                feedbox.children().each(function(i,child){feedbox.prepend(child)});
                feedbox.prop('scrollTop',scroll);
                $("#topDown").removeClass('selected');
            }
        });
        $("#media-selector").click(function(){
            if($(this).hasClass('selected')) return;
            else {
                $.ajax({url:yii.baseUrl+"/index.php/site/mediaWidgetToggle"});
                yii.profile['mediaWidgetDrive']=0;
                $(this).addClass('selected');
                $(this).removeClass('hover');
                $("#media-widget-gear-menu").removeClass('open');
                $("#drive-selector").removeClass('selected');
                $('#drive-table').hide();
                $('#x2-media-list').show();
            }
        });
        $("#drive-selector").click(function(){
            if($(this).hasClass('selected')) return;
            else {
                $.ajax({url:yii.baseUrl+"/index.php/site/mediaWidgetToggle"});
                yii.profile['mediaWidgetDrive']=1;
                $(this).addClass('selected');
                $(this).removeClass('hover');
                $("#media-widget-gear-menu").removeClass('open');
                $("#media-selector").removeClass('selected');
                $('#drive-table').show();
                $('#x2-media-list').hide();
            }
        });
        $("#drive-refresh").click(function(e){
            e.preventDefault();
            $.ajax({
                'url':'<?php echo Yii::app()->controller->createUrl('/media/media/refreshDriveCache') ?>',
                'success':function(data){
                    $('#drive-table').html(data);
                }
            });
            $("#media-widget-gear-menu").removeClass('open');
        });

        function saveWidgetBgColor () {
            if ($(this).data ('ignoreChange')) {
                return;
            }
            var color = $(this).val();
            $.ajax({
                url: yii.baseUrl + '/index.php/site/activityFeedWidgetBgColor',
                data: 'color='+ color,
                success:function(){
                    if(color == '') {
                        $('#feed-box').css('background-color', '#fff');
                    } else {
                        $('#feed-box').css('background-color', '#' + color);
                    }
                    //$('#feed-box').css("color", convertTextColor(color, 'standardText'));
                    // Check for a dark color
                    /*if(convertTextColor(color, 'linkText') == '#fff000'){
                    $('#feed-box a').removeClass();
                    $('#feed-box a').addClass('dark_background');
                }
                // Light color
                else {
                    $('#feed-box a').removeClass();
                    $('#feed-box a').addClass("light_background");
                }
                // Set color correctly if transparent is selected
                if(color == ""){
                    $('#feed-box').css("color", "rgb(51, 51, 51)");
                    $('#feed-box a').removeClass();
                    $('#feed-box a').addClass("light_background");
                }*/
                }
            });
        }

        setupSpectrum ($('#widgets-activity-feed-widget-bg-color'), true);

        $('#widgets-activity-feed-widget-bg-color').change(saveWidgetBgColor);


    });

    // @param $colorString a string representing a hex number
    // @param $testType standardText or linkText
    function convertTextColor( colorString, textType){
        // Split the string to red, green and blue components
        // Convert hex strings into ints
        var red   = parseInt(colorString.substring(1,3), 16);
        var green = parseInt(colorString.substring(3,5), 16);
        var blue  = parseInt(colorString.substring(5,7), 16);

        if(textType == 'standardText') {
            if((((red*299)+(green*587)+(blue*114))/1000) >= 128) {
                return 'black';
            }
            else {
                return 'white';
            }
        }
        else if (textType == 'linkText') {
            if((((red < 100) || (green < 100)) && blue > 80) || ((red < 80) && (green < 80) && (blue < 80))) {
                return '#fff000';  // Yellow links
            }
            else return '#0645AD'; // Blue link color
        }
        else if (textType == 'visitedLinkText') {
            if((((red < 100) || (green < 100)) && blue > 80) || ((red < 80) && (green < 80) && (blue < 80))) {
                return '#ede100';  // Yellow links
            }
            else return '#0B0080'; // Blue link color
        }
        else if (textType == 'activeLinkText') {
            if((((red < 100) || (green < 100)) && blue > 80) || ((red < 80) && (green < 80) && (blue < 80))) {
                return '#fff000';  // Yellow links
            }
            else return '#0645AD'; // Blue link color
        }
        else if (textType == 'hoverLinkText') {
            if((((red < 100) || (green < 100)) && blue > 80) || ((red < 80) && (green < 80) && (blue < 80))) {
                return '#fff761';  // Yellow links
            }
            else return '#3366BB'; // Blue link color
        }
    }
</script>
<style>
    .dark_background:link { color: #fff000 !important; }
    .dark_background:active { color: #fff000 !important; }
    .dark_background:hover { color: #F0E030 !important; }
    .dark_background:visited { color: #ede100 !important; }

    .light_background:link { color: #0645AD !important; }
    .light_background:active { color: #0645AD !important; }
    .light_background:hover { color: #3366BB !important; }
    .light_background:visited { color: #0B0080 !important; }
</style>
