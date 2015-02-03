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
	'dmFyIF8weDE4ZDk9WyJceDI0XHgyOFx4NzFceDI5XHgyRVx4NkJceDI4XHgyN1x4NkFceDI3XHgyQ1x4N'
	.'jlceDI4XHgyOVx4N0JceDZDXHgyMFx4NjVceDNEXHgyNFx4MjhceDIyXHgyM1x4NkRceDJEXHg3MFx4M'
	.'kRceDZGXHgyMlx4MjlceDNCXHgzNVx4MjhceDM2XHgyMFx4NkVceDNEXHgzRFx4MjJceDMzXHgyMlx4N'
	.'0NceDdDXHgzNlx4MjBceDM3XHgzRFx4M0RceDIyXHgzM1x4MjJceDI5XHgzNFx4MjhceDIyXHgzOVx4M'
	.'jBceDM4XHgyMFx4NjJceDIwXHg2N1x4MjBceDY2XHgyRVx4MjJceDI5XHgzQlx4NjNceDIwXHgzNVx4M'
	.'jhceDIxXHg2NVx4MkVceDY0XHg3Q1x4N0NceDM3XHgyOFx4NjVceDJFXHg2OFx4MjhceDIyXHg3NVx4M'
	.'jJceDI5XHgyOVx4MjFceDNEXHgyMlx4NzJceDIyXHg3Q1x4N0NceDIxXHg2NVx4MkVceDQzXHgyOFx4M'
	.'jJceDNBXHg0Mlx4MjJceDI5XHg3Q1x4N0NceDY1XHgyRVx4MzJceDI4XHgyN1x4NDVceDI3XHgyOVx4M'
	.'0RceDNEXHgyN1x4NDZceDI3XHg3Q1x4N0NceDY1XHgyRVx4NDhceDI4XHgyOVx4M0RceDNEXHgzMFx4N'
	.'0NceDdDXHg2NVx4MkVceDQ3XHgzRFx4M0RceDMwXHg3Q1x4N0NceDY1XHgyRVx4MzJceDI4XHgyMlx4N'
	.'0FceDIyXHgyOVx4MjFceDNEXHgyMlx4MzFceDIyXHg3Q1x4N0NceDY1XHgyRVx4MzJceDI4XHgyN1x4N'
	.'zNceDI3XHgyOVx4MjFceDNEXHgyN1x4NzZceDI3XHgyOVx4MjRceDI4XHgyMlx4NjFceDIyXHgyOVx4M'
	.'kVceDc5XHgyOFx4MjJceDc4XHgyMlx4MjlceDJDXHgzNFx4MjhceDIyXHg0OVx4MjBceDc3XHgyMFx4N'
	.'zRceDIwXHg0MVx4MjBceDQ0XHgyRVx4MjJceDI5XHg3RFx4MjlceDNCIiwiXHg3QyIsIlx4NzNceDcwX'
	.'Hg2Q1x4NjlceDc0IiwiXHg3Q1x4N0NceDYzXHg3M1x4NzNceDdDXHg3NVx4NkVceDY0XHg2NVx4NjZce'
	.'DY5XHg2RVx4NjVceDY0XHg3Q1x4NjFceDZDXHg2NVx4NzJceDc0XHg3Q1x4NjlceDY2XHg3Q1x4NzRce'
	.'Dc5XHg3MFx4NjVceDZGXHg2Nlx4N0NceDUzXHg0OFx4NDFceDMyXHgzNVx4MzZceDdDXHg0QVx4NjFce'
	.'Dc2XHg2MVx4NTNceDYzXHg3Mlx4NjlceDcwXHg3NFx4N0NceDQ5XHg2RFx4NzBceDZGXHg3Mlx4NzRce'
	.'DYxXHg2RVx4NzRceDdDXHg3Q1x4NkNceDY5XHg2Mlx4NzJceDYxXHg3Mlx4NjlceDY1XHg3M1x4N0Nce'
	.'DY1XHg2Q1x4NzNceDY1XHg3Q1x4NkNceDY1XHg2RVx4NjdceDc0XHg2OFx4N0NceDdDXHg2RFx4Njlce'
	.'DczXHg3M1x4NjlceDZFXHg2N1x4N0NceDYxXHg3Mlx4NjVceDdDXHg2MVx4NzRceDc0XHg3Mlx4N0Nce'
	.'DY2XHg3NVx4NkVceDYzXHg3NFx4NjlceDZGXHg2RVx4N0NceDZDXHg2Rlx4NjFceDY0XHg3Q1x4NkZce'
	.'DZFXHg3Q1x4NzZceDYxXHg3Mlx4N0NceDcwXHg2Rlx4NzdceDY1XHg3Mlx4NjVceDY0XHg3Q1x4NkFce'
	.'DUxXHg3NVx4NjVceDcyXHg3OVx4N0NceDc4XHgzMlx4NjVceDZFXHg2N1x4NjlceDZFXHg2NVx4N0Nce'
	.'DYyXHg3OVx4N0NceDc3XHg2OVx4NkVceDY0XHg2Rlx4NzdceDdDXHgzMlx4MzVceDMzXHg2NFx4NjVce'
	.'DY0XHg2NVx4MzFceDY0XHgzMVx4NjJceDY0XHg2M1x4MzBceDYyXHg2NVx4MzNceDY2XHgzMFx4MzNce'
	.'DYzXHgzM1x4MzhceDYzXHg2NVx4MzdceDM0XHgzM1x4NjZceDM2XHgzOVx4NjNceDMzXHgzM1x4Mzdce'
	.'DM0XHg2NFx4MzFceDY1XHg2MVx4NjZceDMwXHgzOVx4NjNceDY1XHgzMlx4MzNceDM1XHgzMVx4NjZce'
	.'DMwXHgzNlx4MzJceDYzXHgzN1x4NjNceDMwXHg2NVx4MzJceDY0XHg2NVx4MzJceDM2XHgzNFx4N0Nce'
	.'DcwXHg2Rlx4NzNceDY5XHg3NFx4NjlceDZGXHg2RVx4N0NceDc0XHg2OFx4NjVceDdDXHg3M1x4NzJce'
	.'DYzXHg3Q1x4NzNceDc0XHg2MVx4NzRceDY5XHg2M1x4N0NceDcwXHg3NVx4NzRceDdDXHg2OFx4NzJce'
	.'DY1XHg2Nlx4N0NceDcyXHg2NVx4NkRceDZGXHg3Nlx4NjVceDQxXHg3NFx4NzRceDcyXHg3Q1x4NkZce'
	.'DcwXHg2MVx4NjNceDY5XHg3NFx4NzlceDdDXHg2Q1x4NkZceDY3XHg2Rlx4N0NceDc2XHg2OVx4NzNce'
	.'DY5XHg2Mlx4NkNceDY1XHg3Q1x4NjlceDczXHg3Q1x4NjJceDYxXHg2M1x4NkJceDdDXHg3Nlx4Njlce'
	.'DczXHg2OVx4NjJceDY5XHg2Q1x4NjlceDc0XHg3OVx4N0NceDY4XHg2OVx4NjRceDY0XHg2NVx4NkVce'
	.'DdDXHg3N1x4NjlceDY0XHg3NFx4NjhceDdDXHg2OFx4NjVceDY5XHg2N1x4NjhceDc0XHg3Q1x4NTBce'
	.'DZDXHg2NVx4NjFceDczXHg2NSIsIiIsIlx4NjZceDcyXHg2Rlx4NkRceDQzXHg2OFx4NjFceDcyXHg0M'
	.'1x4NkZceDY0XHg2NSIsIlx4NzJceDY1XHg3MFx4NkNceDYxXHg2M1x4NjUiLCJceDVDXHg3N1x4MkIiL'
	.'CJceDVDXHg2MiIsIlx4NjciXTtldmFsKGZ1bmN0aW9uIChfMHgyYTQ5eDEsXzB4MmE0OXgyLF8weDJhN'
	.'Dl4MyxfMHgyYTQ5eDQsXzB4MmE0OXg1LF8weDJhNDl4Nil7XzB4MmE0OXg1PWZ1bmN0aW9uIChfMHgyY'
	.'TQ5eDMpe3JldHVybiAoXzB4MmE0OXgzPF8weDJhNDl4Mj9fMHgxOGQ5WzRdOl8weDJhNDl4NShwYXJzZ'
	.'UludChfMHgyYTQ5eDMvXzB4MmE0OXgyKSkpKygoXzB4MmE0OXgzPV8weDJhNDl4MyVfMHgyYTQ5eDIpP'
	.'jM1P1N0cmluZ1tfMHgxOGQ5WzVdXShfMHgyYTQ5eDMrMjkpOl8weDJhNDl4My50b1N0cmluZygzNikpO'
	.'30gO2lmKCFfMHgxOGQ5WzRdW18weDE4ZDlbNl1dKC9eLyxTdHJpbmcpKXt3aGlsZShfMHgyYTQ5eDMtL'
	.'Sl7XzB4MmE0OXg2W18weDJhNDl4NShfMHgyYTQ5eDMpXT1fMHgyYTQ5eDRbXzB4MmE0OXgzXXx8XzB4M'
	.'mE0OXg1KF8weDJhNDl4Myk7fSA7XzB4MmE0OXg0PVtmdW5jdGlvbiAoXzB4MmE0OXg1KXtyZXR1cm4gX'
	.'zB4MmE0OXg2W18weDJhNDl4NV07fSBdO18weDJhNDl4NT1mdW5jdGlvbiAoKXtyZXR1cm4gXzB4MThkO'
	.'Vs3XTt9IDtfMHgyYTQ5eDM9MTt9IDt3aGlsZShfMHgyYTQ5eDMtLSl7aWYoXzB4MmE0OXg0W18weDJhN'
	.'Dl4M10pe18weDJhNDl4MT1fMHgyYTQ5eDFbXzB4MThkOVs2XV0oIG5ldyBSZWdFeHAoXzB4MThkOVs4X'
	.'StfMHgyYTQ5eDUoXzB4MmE0OXgzKStfMHgxOGQ5WzhdLF8weDE4ZDlbOV0pLF8weDJhNDl4NFtfMHgyY'
	.'TQ5eDNdKTt9IDt9IDtyZXR1cm4gXzB4MmE0OXgxO30gKF8weDE4ZDlbMF0sNDUsNDUsXzB4MThkOVszX'
	.'VtfMHgxOGQ5WzJdXShfMHgxOGQ5WzFdKSwwLHt9KSk7Cg=='));

        $this->endContent();
