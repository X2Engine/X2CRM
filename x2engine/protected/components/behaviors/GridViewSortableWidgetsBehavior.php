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




/**
 * Used to display gridviews within sortable widgets. This allows sortable widget gridviews to have
 * their own results per page settings.
 * @package application.components
 */
class GridViewSortableWidgetsBehavior extends CBehavior {

    /**
     * @var object an instance of SortableWidget 
     */
    public $sortableWidget;

    /**
     * Called by X2GridView's init () method to set the summaryText property. This method allows
     * the results per page drop down to display/set the results per page for an individual 
     * sortable widget
     */
    public function setSummaryTextForSortableWidgets () {
        $widgetClass = get_class ($this->owner->sortableWidget);
        $resultsPerPage = $widgetClass::getJSONProperty (
            $this->owner->sortableWidget->profile, 'resultsPerPage', 
            $this->owner->sortableWidget->widgetType, $this->owner->sortableWidget->widgetUID);

        // add a dropdown to the summary text that let's user set how many rows to show on each page
        $this->owner->summaryText = 
            '<span class="grid-summary-container" style="'.
                ($this->owner->hideSummary ? 'display: none;' : '').'">'.
            Yii::t('app','<b>{start}&ndash;{end}</b> of <b>{count}</b>').
            '<div class="form no-border" style="display:inline;">'.
            CHtml::dropDownList(
                $widgetClass.'resultsPerPage', 
                $resultsPerPage,
                $this->owner->getPossibleResultsPerPageFormatted (), 
                array(
                    'class' => 'x2-minimal-select',
                    'onchange' => '$.ajax ({'.
                        'data: {'.
                            'key: "resultsPerPage",'.
                            'value: $(this).val(),'.
                            'widgetClass: "'.get_class ($this->owner->sortableWidget).'",'.
                            'widgetType: "'.$this->owner->sortableWidget->widgetType.'",'.
                            'widgetUID: "'.$this->owner->sortableWidget->widgetUID.'"'.
                        '},'.
                        'type: "POST",'.
                        'url: "'.Yii::app()->controller
                            ->createUrl('/profile/setWidgetSetting').'",'.
                        'complete: function (response) {'.
                            'x2.DEBUG && console.log ("setResultsPerPage after ajax");'.
                            '$.fn.yiiGridView.update("'.$this->owner->id.'", {'.
                                (isset($this->owner->modelName) ?
                                    'data: {'.$this->owner->modelName.'_page: 1},' : '') .
                                    'complete: function () {'.
                                    '}'.
                            '});'.
                        '}'.
                    '});'
                )). 
            '</div>
            </span>';
    }

}
?>
