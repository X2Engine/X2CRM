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
 * Sublclasses classes defined in x2gridview.js
 */
(function($) {

$.widget('x2.summationReportGridResizing', $.x2.reportGridResizing, {
    _create:function() {
        console.log ('gridReportGridResizing create');
        var that = this;
        this._super ();
    },
    _destroy:function() {
        this._super ();
        this.element.removeData('x2-summationReportGridResizing');
    },
    /**
     * Override parent method so to handle expand button column width as a special case
     */
    scanColWidths:function() {
        // temporarily set display of header table to block, causing widths of header cells to
        // automatically resize
        this.t1.table.css ({display: 'block'});
        this.colWidths = []; // clear previous stuff
        var colCount = this.t1.masterCells.length;
        if(this.options.ignoreLastCol)
            colCount--;
        if (this.t1.masterCells.eq (0).attr ('id').match (/subgrid-expand-button-column/)) { 
            var i = 1;
            var expandButtonWidth = 27;
            this.t1.masterCells.eq(0).width (expandButtonWidth);
            this.colWidths.push (expandButtonWidth);
        } else {
            var i = 0;
        } 
        for(;i<colCount;i++) {
            var cell = this.t1.masterCells.eq(i);
            if (typeof $(cell).attr ('style') !== 'undefined') {
                var w = Math.max(this.options.minColWidth,cell.width());
            } else {
                var w = Math.max(this.options.minColWidth,cell.width() + 15);
            }
            this.colWidths.push(w);
        }

        // remove temporary styling
        this.t1.table.css ({display: ''});
    },


});

/**
 * Subclass parent so that gridResizingClass can be swapped for x2.gridReportGridResizing
 */
$.widget("x2.summationReportGvSettings", $.x2.reportGridSettings, {
    options: {
        // swap class dependencies
        gridResizingClass: 'summationReportGridResizing',
        reportConfig: {}
    },
    _create:function() {
        //console.log ('summationReportGvSettings');
        this._setUpSubgridButtonBehavior ();
        this._super ();
    },
    /**
     * Set up behavior of sub grid expand/collapse buttons 
     */
    _setUpSubgridButtonBehavior: function () {
        var that = this;

        // show/request sub grid
        this.element.find ('.subgrid-expand-button').unbind ('click._setUpSubgridButtonBehavior')
            .bind ('click._setUpSubgridButtonBehavior', function () {
        
            var groupAttrValues = JSON.parse ($(this).attr ('data-group-attr-values'));
            var subgridRow$ = $(this).closest ('tr').next ('.x2-subgrid-row');
            if (subgridRow$.length) { // requested before, just show it
                subgridRow$.show ();
                $(this).hide ();
                $(this).next ().show ();
            } else { 
                that._requestSubgrid (groupAttrValues, $(this).closest ('tr'));
            }
        });

        // hide sub grid
        this.element.find ('.subgrid-collapse-button').unbind ('click._setUpSubgridButtonBehavior')
            .bind ('click._setUpSubgridButtonBehavior', function () {
        
            $(this).closest ('tr').next ('.x2-subgrid-row').hide ();
            $(this).hide ();
            $(this).prev ().show ();
        });
    },
    /**
     * Request drill down sub grid and insert into parent grid
     * @param object groupAttrValues 
     */
    _requestSubgrid: function (groupAttrValues, currRow$) {
        console.log ('_requestSubgrid');
        console.log (this.options.reportConfig);

        // add options to get params
        for (var key in this.options.reportConfig) {
            if (key.match (/.*FormModel$/)) {
                var formName = key;
                break;
            }
        }

        var params = $.extend (true, {}, this.options.reportConfig);
        params[formName]['groupAttrValues'] = groupAttrValues;
        params[formName]['generateSubgrid'] = 1;
        params[formName]['subgridIndex'] = currRow$.index ();
        console.log ('formName = ');
            console.log (formName);


        $.ajax ({
            url: window.location + '?' + $.param (params),
            dataType: 'json',
            success: function (data) {
                var subgridRow$ = $('<tr>', {
                    'class': 'x2-subgrid-row'
                }); 
                var subgridCell$ = $('<td>', {
                    colspan: currRow$.children ('td').length 
                }); 
                subgridRow$.append (subgridCell$);
                currRow$.after (subgridRow$);
                subgridCell$.html (data.report);
                currRow$.find ('.subgrid-expand-button').hide ();
                currRow$.find ('.subgrid-collapse-button').show ();
            }
        });
    }
});

})(jQuery);
