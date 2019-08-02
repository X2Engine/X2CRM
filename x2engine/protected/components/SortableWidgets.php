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





Yii::import('zii.widgets.jui.CJuiWidget');

/**
 * CJuiSortable class.
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @package application.components
 */
class SortableWidgets extends CJuiWidget {

    /**
     * @var array list of sortable items (id=>item content).
     * Note that the item contents will not be HTML-encoded.
     */
    public $portlets = array();
    public $jQueryOptions = array();

    /**
     * @var string the name of the container element that contains all items. Defaults to 'div'.
     */
    public $tagName = 'div';

    /**
     * Run this widget.
     * This method registers necessary javascript and renders the needed HTML code.
     */
    public function run(){
        $themeURL = Yii::app()->theme->getBaseUrl();

        Yii::app()->clientScript->registerScript('toggleWidgetState', "
            function toggleWidgetState(widget,state) {
                if($('#widget_' + widget).hasClass('ui-sortable-helper') == false) {
                    $.ajax({
                        url: '".CHtml::normalizeUrl(array('/site/widgetState'))."',
                        type: 'GET',
                        data: 'widget='+widget+'&state='+state,
                        success: function(response) {
                            if(response.trim() === 'success') {
                                var link = $('#widget_'+widget+
                                    ' .portlet-minimize a.portlet-minimize-button');
                                var newLink = ($(link).find('span').hasClass('expand-widget')) ?
                                    '<span '+ 
                                      'class=\"fa fa-caret-down collapse-widget\" ></span>' : 
                                    // toggle link between [+] and [-]
                                    '<span '+
                                      'class=\"fa fa-caret-left expand-widget\"></span>';            
                                link.html(newLink);

                                // slide widget open or closed
                                $('#widget_'+widget+' .portlet-content').toggle({
                                    effect: 'blind',
                                    duration: 200,
                                    complete: function() {
                                        blindComplete = true;
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
        if(isset($this->htmlOptions['id'])) {
            $id = $this->htmlOptions['id'];
        } else {
            $this->htmlOptions['id'] = $id;
        }

        $options = empty($this->jQueryOptions) ? '' : CJavaScript::encode($this->jQueryOptions);
        Yii::app()->getClientScript()->registerScript(
            'SortableWidgets'.'#'.$id, "jQuery('#{$id}').sortable({$options});");

        echo CHtml::openTag($this->tagName, $this->htmlOptions)."\n";

        $widgetHideList = array();
        if(!Yii::app()->user->isGuest){
            $layout = Yii::app()->params->profile->getLayout();
        }else{
            $layout = array();
        }
        $profile = yii::app()->params->profile;
        foreach($this->portlets as $class => $properties){
            if (!class_exists ($class)) continue;
            
            // show widget if it isn't hidden
            if(!in_array($class, array_keys($layout['hiddenRight']))){ 
                $visible = ($properties['visibility'] == '1');

                if(!$visible)
                    $widgetHideList[] = '#widget_'.$class;

                $minimizeLink = CHtml::link(
                    $visible ? 
                        CHtml::tag('span',
                            array('class' => 'fa fa-caret-down collapse-widget'), ' ') : 

                        CHtml::tag('span',
                            array('class' => 'fa fa-caret-left expand-widget'), ' ')

                    , '#', array('class' => 'portlet-minimize-button')
                    ).' '.CHtml::link(
                        '<i class="fa fa-times"></i>', '#',
                        array(
                            'onclick' => "$('#widget_$class').hideWidgetRight(); return false;",
                            'class' => 'portlet-close-button'
                        )
                    );

                $widget = $this->widget($class, $properties['params'], true);

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
                    $activityFeedWidgetBgColor = $preferences['activityFeedWidgetBgColor']; 
                }
                if(!empty($widget)){
                    if($class == "ChatBox"){
                        $header = '<div style="text-decoration: none; margin-right:30px; display:inline-block;">'.
                            Yii::t('app', 'Activity Feed').
                            '</div>
                            <script>
                                $(\'#widget-dropdown a\').css("text-align", "none");
                                $(\'#widget-dropdown a\').css("text-align", "center !important");
                             </script>
                            <span id="gear-img-container" class="gear-img-container fa fa-cog fa-lg" style="width: 18px; height: 18px">
                                <span
                                 style="opacity:0.3" onmouseout="this.style.opacity=0.3;"
                                 onmouseover="this.style.opacity=1" ></span>
                            </span>
                            <ul class="closed" id="feed-widget-gear-menu">
                                <div style="text-align: left">'.
                                    Yii::t('app','Activity Feed Order').
                                '</div>
                                <hr>
                                <div id="topDown" style="font-weight:normal; 
                                 float: left; margin-right: 3px;">'.
                                    Yii::t('app','Top Down').
                                '</div>
                                <div id="bottomUp" style="font-weight:normal; float: left">'.
                                    Yii::t('app','Bottom Up').
                                '</div>
                                <!--hr>
                                <div style="text-align: left">'.
                                    Yii::t('app','Background Color').
                                '</div>
                                <colorPicker style="padding: 0px !important;">'.
                                    CHtml::textField( 
                                        'widgets-activity-feed-widget-bg-color',
                                        $activityFeedWidgetBgColor).
                                '</colorPicker-->
                            </ul>';
                    }elseif($class == "MediaBox" && Yii::app()->settings->googleIntegration){
                        $auth = new GoogleAuthenticator();
                        if($auth->getAccessToken()){
                            $header = 
                                '<div style="display:inline-block;">'.
                                    Yii::t('app', 'Media').
                                '</div>
                                <span class="gear-img-container fa fa-cog fa-lg">
                                </span>
                                <ul class="closed" id="media-widget-gear-menu">
                                    <div style="text-align: left">'.
                                        Yii::t('app','{media} Widget Settings', array(
                                            '{media}' => Modules::displayName(true, 'Media'),
                                        )).
                                    '</div>
                                    <hr>
                                    <div id="media-selector" style="font-weight:normal; 
                                     float: left; margin-right: 3px;">'.
                                         Yii::t('app','X2 {media}', array(
                                            '{media}' => Modules::displayName(true, 'Media'),
                                         )).
                                    '</div>
                                    <div id="drive-selector" style="font-weight:normal; 
                                     float: left">'.
                                        Yii::t('app','Google Drive').
                                    '</div>
                                    <hr>
                                    <div style="text-align: left">'.
                                        Yii::t('app','Refresh Google Drive Cache').
                                    '</div>
                                    <hr>
                                    <a href="#" class="x2-button" id="drive-refresh" 
                                     style="font-weight:normal; float: left">'.
                                        Yii::t('app','Refresh Files').
                                    '</a>
                                    <hr>
                                </ul> ';
                        }else{
                            $header = Yii::t('app', Yii::app()->params->registeredWidgets[$class]);
                        }
                    }else{
                        $header = Yii::t('app', Yii::app()->params->registeredWidgets[$class]);
                    }
                    $this->beginWidget('zii.widgets.CPortlet', array(
                        'title' => 
                            '<div id="widget-dropdown" class="dropdown">'
                                .$header.
                                '<div class="portlet-minimize" 
                                  onclick="toggleWidgetState(\''.
                                    $class.'\','.($visible ? 0 : 1).'); return false;">'.

                                    $minimizeLink.
                                '</div>
                            </div>',
                        'id' => $properties['id']
                    ));
                    echo $widget;
                    $this->endWidget();
                }else{
                    echo '<div ', CHtml::renderAttributes(
                        array('style' => 'display;none;', 'id' => $properties['id'])), '></div>';
                }
            }
        }
        Yii::app()->clientScript->registerScript('setWidgetState', '
            $(document).ready(function() {
                $("'.implode(',', $widgetHideList).'").find(".portlet-content").hide();
            });', CClientScript::POS_HEAD);

        echo CHtml::closeTag($this->tagName);
        
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


    Yii::app()->clientScript->registerScript('sortableWidgetsJS',"
    $(document).ready(function() {
        $('#topDown').hover(function(){
            if(!$(this).hasClass('selected')){
                $(this).toggleClass('hover');
            }
        });
        $('#bottomUp').hover(function(){
            if(!$(this).hasClass('selected')){
                $(this).toggleClass('hover');
            }
        });
        $('#media-selector').hover(function(){
            if(!$(this).hasClass('selected')){
                $(this).toggleClass('hover');
            }
        });
        $('#drive-selector').hover(function(){
            if(!$(this).hasClass('selected')){
                $(this).toggleClass('hover');
            }
        });
        $('#topDown').click(function(){
            if($(this).hasClass('selected')) return;
            else {
                $.ajax({url:yii.baseUrl+'/index.php/site/activityFeedOrder'});
                yii.profile['activityFeedOrder']=1;
                $(this).addClass('selected');
                $(this).removeClass('hover');
                var feedbox = $('#feed-box');
                feedbox.children().each(function(i,child){feedbox.prepend(child)});
                feedbox.prop('scrollTop',0);
                $('#bottomUp').removeClass('selected');
            }
        });
        $('#bottomUp').click(function(){
            if($(this).hasClass('selected')) return;
            else {
                $.ajax({url:yii.baseUrl+'/index.php/site/activityFeedOrder'});
                yii.profile['activityFeedOrder']=0;
                $(this).addClass('selected');
                $(this).removeClass('hover');
                var feedbox = $('#feed-box');
                var scroll=feedbox.prop('scrollHeight');
                feedbox.children().each(function(i,child){feedbox.prepend(child)});
                feedbox.prop('scrollTop',scroll);
                $('#topDown').removeClass('selected');
            }
        });
        $('#media-selector').click(function(){
            if($(this).hasClass('selected')) return;
            else {
                $.ajax({url:yii.baseUrl+'/index.php/site/mediaWidgetToggle'});
                yii.profile['mediaWidgetDrive']=0;
                $(this).addClass('selected');
                $(this).removeClass('hover');
                $('#media-widget-gear-menu').removeClass('open');
                $('#drive-selector').removeClass('selected');
                $('#drive-table').hide();
                $('#x2-media-list').show();
            }
        });
        $('#drive-selector').click(function(){
            if($(this).hasClass('selected')) return;
            else {
                $.ajax({url:yii.baseUrl+'/index.php/site/mediaWidgetToggle'});
                yii.profile['mediaWidgetDrive']=1;
                $(this).addClass('selected');
                $(this).removeClass('hover');
                $('#media-widget-gear-menu').removeClass('open');
                $('#media-selector').removeClass('selected');
                $('#drive-table').show();
                $('#x2-media-list').hide();
            }
        });
        $('#drive-refresh').click(function(e){
            e.preventDefault();
            $.ajax({
                'url':'".
                    Yii::app()->controller->createUrl('/media/media/refreshDriveCache') 
                ."',
                'success':function(data){
                    $('#drive-table').html(data);
                }
            });
            $('#media-widget-gear-menu').removeClass('open');
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
                    //$('#feed-box').css('color', convertTextColor(color, 'standardText'));
                    // Check for a dark color
                    /*if(convertTextColor(color, 'linkText') == '#fff000'){
                    $('#feed-box a').removeClass();
                    $('#feed-box a').addClass('dark_background');
                }
                // Light color
                else {
                    $('#feed-box a').removeClass();
                    $('#feed-box a').addClass('light_background');
                }
                // Set color correctly if transparent is selected
                if(color == ''){
                    $('#feed-box').css('color', 'rgb(51, 51, 51)');
                    $('#feed-box a').removeClass();
                    $('#feed-box a').addClass('light_background');
                }*/
                }
            });
        }

        x2.colorPicker.setUp ($('#widgets-activity-feed-widget-bg-color'), true);

        $('#widgets-activity-feed-widget-bg-color').change(saveWidgetBgColor);


    });

    // @param \$colorString a string representing a hex number
    // @param \$testType standardText or linkText
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
            if((((red < 100) || (green < 100)) && blue > 80) || 
               ((red < 80) && (green < 80) && (blue < 80))) {
                return '#fff000';  // Yellow links
            }
            else return '#0645AD'; // Blue link color
        }
        else if (textType == 'visitedLinkText') {
            if((((red < 100) || (green < 100)) && blue > 80) || 
               ((red < 80) && (green < 80) && (blue < 80))) {
                return '#ede100';  // Yellow links
            }
            else return '#0B0080'; // Blue link color
        }
        else if (textType == 'activeLinkText') {
            if((((red < 100) || (green < 100)) && blue > 80) || 
               ((red < 80) && (green < 80) && (blue < 80))) {
                return '#fff000';  // Yellow links
            }
            else return '#0645AD'; // Blue link color
        }
        else if (textType == 'hoverLinkText') {
            if((((red < 100) || (green < 100)) && blue > 80) || 
               ((red < 80) && (green < 80) && (blue < 80))) {
                return '#fff761';  // Yellow links
            }
            else return '#3366BB'; // Blue link color
        }
    }

    ");

    }
}
?>
<script>
</script>
