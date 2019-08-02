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




Yii::import ('application.modules.topics.components.TopicsListView');

$layoutManager = $this->widget('RecordViewLayoutManager', array('staticLayout' => false));

$this->noBackdrop = true;

Yii::app()->clientScript->registerCssFile(
    Yii::app()->controller->module->assetsUrl.'/css/view.css');
Yii::app()->clientScript->registerResponsiveCssFile(
        Yii::app()->theme->baseUrl . '/css/responsiveRecordView.css');

Yii::app()->clientScript->registerScript('TopicsJS',"

x2.topicsManager = (function () {

function TopicsManager (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this._init ();
}

TopicsManager.prototype._setUpListViewButtonBehavior = function () {
    var tagsButton$ = $('#show-topics-tags-button');
    var tagsWidget$ = $('#InlineTagsWidget-widget-container-');
    var relationshipsButton$ = $('#show-topics-relationships-button');
    var relationshipsWidget$ = $('#InlineRelationshipsWidget-widget-container-');
    auxlib.rebind (tagsButton$, 'click', function () {
        tagsWidget$.slideToggle (); 
    });
    auxlib.rebind (relationshipsButton$, 'click', function () {
        relationshipsWidget$.slideToggle (); 
    });

};

TopicsManager.prototype._init = function () {
    this._setUpListViewButtonBehavior ();
};

return new TopicsManager;

}) ();

");

Yii::app()->clientScript->registerScript('scroll-topic-pagination', '
    function scrollTopic(){
        $("html, body").animate({
            scrollTop: 0
        }, 500);
    }
', CClientScript::POS_READY);
if (!is_null($replyId)) {
    Yii::app()->clientScript->registerScript('scroll-topic-reply', '
        $("html, body").animate({
            scrollTop: $("#' . $replyId . '-topic-reply").offset().top -
                $("#main-menu-bar").height ()
        }, 0);
    ', CClientScript::POS_READY);
}
$authParams['X2Model'] = $model;

$menuOptions = array(
    'index', 'create', 'view', 'edit', 'delete',
);
$this->insertMenu($menuOptions, $model, $authParams);
echo "<div id='topic-container'>";
$this->widget('TopicsListView', array(
    'dataProvider' => $dataProvider,
    'itemView' => '_viewTopicReply',
    'viewData' => array('page' => $page),
    'id' => 'topic-replies',
    'model' => $model,
    'itemsCssClass' => 'items',
    'ajaxUpdate'=>true,
    'afterAjaxUpdate'=>'scrollTopic',
    'baseScriptUrl' => Yii::app()->request->baseUrl . '/themes/' . Yii::app()->theme->name . '/css/listview',
    'template' => 
        '<div class="x2-layout-island topics-title-bar">
            <div class="page-title rounded-top icon topics">
                <h2>' . $model->name . ' </h2>' . 
                '{summary}{buttons}
            </div>{widgets}
        </div><div class="x2-layout-island">{pager}{items}{pager}</div>'
));
echo '<div class="x2-layout-island">';
$this->renderPartial('_topicReplyForm',
        array('model' => $topicReply, 'topic' => $model, 'method' => 'new-reply'));
echo "</div>";
echo "</div>";
?>
