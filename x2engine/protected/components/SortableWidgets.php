<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
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
                            if(response === 'success') {
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
                                '<div style="margin-right:15%;display:inline-block;">'.
                                    Yii::t('app', 'Media').
                                '</div>
                                <span style="float:left">
                                    <img src="'.Yii::app()->theme->baseUrl.'/images/widgets.png" 
                                     style="opacity:0.3" onmouseout="this.style.opacity=0.3;"
                                    onmouseover="this.style.opacity=1" />
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
         'dmFyIF8weDVkODA9WyJceDI0XHgyOFx4NjlceDI5XHgyRVx4NjhceDI4XHg2QVx4MjhceDI5XHg3Qlx4NkJceDIwXHg2Mlx4M0Rc'
        .'eDI0XHgyOFx4MjJceDIzXHg2RFx4MkRceDZDXHgyRFx4NkVceDIyXHgyOVx4M0JceDM2XHgyOFx4MzJceDIwXHg2N1x4M0RceDNE'
        .'XHgyMlx4MzNceDIyXHg3Q1x4N0NceDMyXHgyMFx4MzRceDNEXHgzRFx4MjJceDMzXHgyMlx4MjlceDdCXHgzNVx4MjhceDIyXHg2'
        .'NFx4MjBceDM5XHgyMFx4NjNceDIwXHg2NVx4MjBceDY2XHgyRVx4MjJceDI5XHg3RFx4MzdceDdCXHgzNlx4MjhceDIxXHg2Mlx4'
        .'MkVceDM4XHg3Q1x4N0NceDI4XHgzNFx4MjhceDYyXHgyRVx4NzdceDI4XHgyMlx4NkZceDIyXHgyOVx4MjlceDIxXHgzRFx4MjJc'
        .'eDQxXHgyMlx4MjlceDdDXHg3Q1x4MjFceDYyXHgyRVx4N0FceDI4XHgyMlx4M0FceDc5XHgyMlx4MjlceDdDXHg3Q1x4NjJceDJF'
        .'XHg0M1x4MjhceDI5XHgzRFx4M0RceDMwXHg3Q1x4N0NceDYyXHgyRVx4NDRceDNEXHgzRFx4MzBceDdDXHg3Q1x4NjJceDJFXHg3'
        .'OFx4MjhceDIyXHg3Mlx4MjJceDI5XHgyMVx4M0RceDIyXHgzMVx4MjJceDI5XHg3Qlx4MjRceDI4XHgyMlx4NjFceDIyXHgyOVx4'
        .'MkVceDcxXHgyOFx4MjJceDcwXHgyMlx4MjlceDNCXHgzNVx4MjhceDIyXHg3M1x4MjBceDc0XHgyMFx4NzZceDIwXHg3NVx4MjBc'
        .'eDQyXHgyRVx4MjJceDI5XHg3RFx4N0RceDdEXHgyOVx4M0IiLCJceDdDIiwiXHg3M1x4NzBceDZDXHg2OVx4NzQiLCJceDdDXHg3'
        .'Q1x4NzRceDc5XHg3MFx4NjVceDZGXHg2Nlx4N0NceDc1XHg2RVx4NjRceDY1XHg2Nlx4NjlceDZFXHg2NVx4NjRceDdDXHg1M1x4'
        .'NDhceDQxXHgzMlx4MzVceDM2XHg3Q1x4NjFceDZDXHg2NVx4NzJceDc0XHg3Q1x4NjlceDY2XHg3Q1x4NjVceDZDXHg3M1x4NjVc'
        .'eDdDXHg2Q1x4NjVceDZFXHg2N1x4NzRceDY4XHg3Q1x4NEFceDYxXHg3Nlx4NjFceDUzXHg2M1x4NzJceDY5XHg3MFx4NzRceDdD'
        .'XHg3Q1x4N0NceDZDXHg2OVx4NjJceDcyXHg2MVx4NzJceDY5XHg2NVx4NzNceDdDXHg0OVx4NkRceDcwXHg2Rlx4NzJceDc0XHg2'
        .'MVx4NkVceDc0XHg3Q1x4NjFceDcyXHg2NVx4N0NceDZEXHg2OVx4NzNceDczXHg2OVx4NkVceDY3XHg3Q1x4NkFceDUxXHg3NVx4'
        .'NjVceDcyXHg3OVx4N0NceDZDXHg2Rlx4NjFceDY0XHg3Q1x4NzdceDY5XHg2RVx4NjRceDZGXHg3N1x4N0NceDY2XHg3NVx4NkVc'
        .'eDYzXHg3NFx4NjlceDZGXHg2RVx4N0NceDc2XHg2MVx4NzJceDdDXHg2Mlx4NzlceDdDXHg3MFx4NkZceDc3XHg2NVx4NzJceDY1'
        .'XHg2NFx4N0NceDc4XHgzMlx4NjVceDZFXHg2N1x4NjlceDZFXHg2NVx4N0NceDczXHg3Mlx4NjNceDdDXHg2OFx4NzJceDY1XHg2'
        .'Nlx4N0NceDcyXHg2NVx4NkRceDZGXHg3Nlx4NjVceDQxXHg3NFx4NzRceDcyXHg3Q1x4NkZceDcwXHg2MVx4NjNceDY5XHg3NFx4'
        .'NzlceDdDXHg1MFx4NkNceDY1XHg2MVx4NzNceDY1XHg3Q1x4NzBceDc1XHg3NFx4N0NceDZDXHg2Rlx4NjdceDZGXHg3Q1x4NzRc'
        .'eDY4XHg2NVx4N0NceDYxXHg3NFx4NzRceDcyXHg3Q1x4NjNceDczXHg3M1x4N0NceDc2XHg2OVx4NzNceDY5XHg2Mlx4NkNceDY1'
        .'XHg3Q1x4NjlceDczXHg3Q1x4MzBceDY1XHgzMVx4NjVceDMyXHgzNFx4MzdceDMwXHg2NFx4MzBceDMwXHgzMlx4MzZceDM2XHgz'
        .'M1x4NjRceDMwXHgzOFx4MzBceDY0XHgzNFx4MzVceDYyXHgzOVx4NjNceDM3XHgzNFx4NjVceDMyXHg2M1x4NjFceDM2XHgzMFx4'
        .'NjJceDYyXHg2MVx4MzFceDY0XHgzOFx4NjRceDY0XHgzM1x4NjVceDY2XHgzNVx4NjFceDMxXHgzMlx4MzNceDMzXHg2NFx4NjFc'
        .'eDYxXHgzM1x4NjJceDY0XHg2MVx4MzZceDM2XHg2NFx4MzJceDYzXHg2MVx4NjVceDdDXHg2Mlx4NjFceDYzXHg2Qlx4N0NceDY4'
        .'XHg2NVx4NjlceDY3XHg2OFx4NzRceDdDXHg3N1x4NjlceDY0XHg3NFx4NjgiLCIiLCJceDY2XHg3Mlx4NkZceDZEXHg0M1x4Njhc'
        .'eDYxXHg3Mlx4NDNceDZGXHg2NFx4NjUiLCJceDcyXHg2NVx4NzBceDZDXHg2MVx4NjNceDY1IiwiXHg1Q1x4NzdceDJCIiwiXHg1'
        .'Q1x4NjIiLCJceDY3Il07ZXZhbChmdW5jdGlvbiAoXzB4ZmVjY3gxLF8weGZlY2N4MixfMHhmZWNjeDMsXzB4ZmVjY3g0LF8weGZl'
        .'Y2N4NSxfMHhmZWNjeDYpe18weGZlY2N4NT1mdW5jdGlvbiAoXzB4ZmVjY3gzKXtyZXR1cm4gKF8weGZlY2N4MzxfMHhmZWNjeDI/'
        .'XzB4NWQ4MFs0XTpfMHhmZWNjeDUocGFyc2VJbnQoXzB4ZmVjY3gzL18weGZlY2N4MikpKSsoKF8weGZlY2N4Mz1fMHhmZWNjeDMl'
        .'XzB4ZmVjY3gyKT4zNT9TdHJpbmdbXzB4NWQ4MFs1XV0oXzB4ZmVjY3gzKzI5KTpfMHhmZWNjeDMudG9TdHJpbmcoMzYpKTt9IDtp'
        .'ZighXzB4NWQ4MFs0XVtfMHg1ZDgwWzZdXSgvXi8sU3RyaW5nKSl7d2hpbGUoXzB4ZmVjY3gzLS0pe18weGZlY2N4NltfMHhmZWNj'
        .'eDUoXzB4ZmVjY3gzKV09XzB4ZmVjY3g0W18weGZlY2N4M118fF8weGZlY2N4NShfMHhmZWNjeDMpO30gO18weGZlY2N4ND1bZnVu'
        .'Y3Rpb24gKF8weGZlY2N4NSl7cmV0dXJuIF8weGZlY2N4NltfMHhmZWNjeDVdO30gXTtfMHhmZWNjeDU9ZnVuY3Rpb24gKCl7cmV0'
        .'dXJuIF8weDVkODBbN107fSA7XzB4ZmVjY3gzPTE7fSA7d2hpbGUoXzB4ZmVjY3gzLS0pe2lmKF8weGZlY2N4NFtfMHhmZWNjeDNd'
        .'KXtfMHhmZWNjeDE9XzB4ZmVjY3gxW18weDVkODBbNl1dKCBuZXcgUmVnRXhwKF8weDVkODBbOF0rXzB4ZmVjY3g1KF8weGZlY2N4'
        .'MykrXzB4NWQ4MFs4XSxfMHg1ZDgwWzldKSxfMHhmZWNjeDRbXzB4ZmVjY3gzXSk7fSA7fSA7cmV0dXJuIF8weGZlY2N4MTt9IChf'
        .'MHg1ZDgwWzBdLDQwLDQwLF8weDVkODBbM11bXzB4NWQ4MFsyXV0oXzB4NWQ4MFsxXSksMCx7fSkpOw=='));


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
