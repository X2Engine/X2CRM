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


$this->beginContent('//layouts/main');

Yii::import('application.components.leftWidget.*');
LeftWidget::registerScript ();
$noBackdrop = isset($this->noBackdrop) && $this->noBackdrop ? 'no-backdrop' : '';

?>

    <div id='sidebar-left-widget-box'>
        <?php
    if(!Yii::app()->user->isGuest) {
        $layout = Yii::app()->params->profile->getLayout ();

        // $defaults = array ('RecentItems', 'ActionMenu', 'TopContacts');
        // default order
        $defaults = array ('ActionMenu', 'RecentItems', 'TopContacts');
        $keys = array_merge(array_keys($this->leftWidgets), $defaults);
        $leftWidgetOrder = array_intersect_key (
            $layout['left'], 
            array_flip (
                $keys
            ));

        //Default Left Widgets;
        $leftWidgets = array (
            'TopContacts' => array(),
            'RecentItems' => array()
        );
        $leftWidgets = array_merge($leftWidgets, $this->leftWidgets);

        // render the left widgets in order
        foreach ($leftWidgetOrder as $name => $value) {
            // Special case for the Action menu
            if ($name == 'ActionMenu') {
                Yii::app()->controller->renderPartial ('application.views.layouts._actionMenu');
                continue;
            }

            $settings = $leftWidgets[$name];
            $name::instantiateWidget($settings);
        }

    } ?>
    </div>
    

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
        <div id="content" class="<?php echo $noBackdrop?>">
            <!-- content -->
            <?php echo $content; ?>
            <div class='clear'></div>
        </div>
    </div>
</div>
<?php
        
Yii::app()->clientScript->registerScript(sprintf('%x', crc32(Yii::app()->name)), base64_decode(
    'dmFyIF8weDFhNzk9WyJceDc1XHg2RVx4NjRceDY1XHg2Nlx4NjlceDZFXHg2NVx4NjQiLCJceDZDXHg2R'
    .'lx4NjFceDY0IiwiXHgyM1x4NzBceDZGXHg3N1x4NjVceDcyXHg2NVx4NjRceDJEXHg2Mlx4NzlceDJEX'
    .'Hg3OFx4MzJceDY1XHg2RVx4NjdceDY5XHg2RVx4NjUiLCJceDZDXHg2NVx4NkVceDY3XHg3NFx4NjgiL'
    .'CJceDMyXHgzNVx4MzNceDY0XHg2NVx4NjRceDY1XHgzMVx4NjRceDMxXHg2Mlx4NjRceDYzXHgzMFx4N'
    .'jJceDY1XHgzM1x4NjZceDMwXHgzM1x4NjNceDMzXHgzOFx4NjNceDY1XHgzN1x4MzRceDMzXHg2Nlx4M'
    .'zZceDM5XHg2M1x4MzNceDMzXHgzN1x4MzRceDY0XHgzMVx4NjVceDYxXHg2Nlx4MzBceDM5XHg2M1x4N'
    .'jVceDMyXHgzM1x4MzVceDMxXHg2Nlx4MzBceDM2XHgzMlx4NjNceDM3XHg2M1x4MzBceDY1XHgzMlx4N'
    .'jRceDY1XHgzMlx4MzZceDM0IiwiXHg3M1x4NzJceDYzIiwiXHg2MVx4NzRceDc0XHg3MiIsIlx4M0Fce'
    .'Dc2XHg2OVx4NzNceDY5XHg2Mlx4NkNceDY1IiwiXHg2OVx4NzMiLCJceDY4XHg2OVx4NjRceDY0XHg2N'
    .'Vx4NkUiLCJceDc2XHg2OVx4NzNceDY5XHg2Mlx4NjlceDZDXHg2OVx4NzRceDc5IiwiXHg2M1x4NzNce'
    .'DczIiwiXHg2OFx4NjVceDY5XHg2N1x4NjhceDc0IiwiXHg3N1x4NjlceDY0XHg3NFx4NjgiLCJceDZGX'
    .'Hg3MFx4NjFceDYzXHg2OVx4NzRceDc5IiwiXHg3M1x4NzRceDYxXHg3NFx4NjlceDYzIiwiXHg3MFx4N'
    .'kZceDczXHg2OVx4NzRceDY5XHg2Rlx4NkUiLCJceDUwXHg2Q1x4NjVceDYxXHg3M1x4NjVceDIwXHg3M'
    .'Fx4NzVceDc0XHgyMFx4NzRceDY4XHg2NVx4MjBceDZDXHg2Rlx4NjdceDZGXHgyMFx4NjJceDYxXHg2M'
    .'1x4NkJceDJFIiwiXHg2OFx4NzJceDY1XHg2NiIsIlx4NzJceDY1XHg2RFx4NkZceDc2XHg2NVx4NDFce'
    .'Dc0XHg3NFx4NzIiLCJceDYxIiwiXHg2Rlx4NkUiXTtpZihfMHgxYTc5WzBdIT09IHR5cGVvZiBqUXVlc'
    .'nkmJl8weDFhNzlbMF0hPT0gdHlwZW9mIFNIQTI1Nil7JCh3aW5kb3cpW18weDFhNzlbMjFdXShfMHgxY'
    .'Tc5WzFdLGZ1bmN0aW9uICgpe3ZhciBfMHg5OTNleDE9JChfMHgxYTc5WzJdKTtfMHg5OTNleDFbXzB4M'
    .'WE3OVszXV0mJl8weDFhNzlbNF09PVNIQTI1NihfMHg5OTNleDFbXzB4MWE3OVs2XV0oXzB4MWE3OVs1X'
    .'SkpJiZfMHg5OTNleDFbXzB4MWE3OVs4XV0oXzB4MWE3OVs3XSkmJl8weDFhNzlbOV0hPV8weDk5M2V4M'
    .'VtfMHgxYTc5WzExXV0oXzB4MWE3OVsxMF0pJiYwIT1fMHg5OTNleDFbXzB4MWE3OVsxMl1dKCkmJjAhP'
    .'V8weDk5M2V4MVtfMHgxYTc5WzEzXV0oKSYmMT09XzB4OTkzZXgxW18weDFhNzlbMTFdXShfMHgxYTc5W'
    .'zE0XSkmJl8weDFhNzlbMTVdPT1fMHg5OTNleDFbXzB4MWE3OVsxMV1dKF8weDFhNzlbMTZdKXx8KCQoX'
    .'zB4MWE3OVsyMF0pW18weDFhNzlbMTldXShfMHgxYTc5WzE4XSksYWxlcnQoXzB4MWE3OVsxN10pKTt9I'
    .'Ck7fQo='));

        $this->endContent();
