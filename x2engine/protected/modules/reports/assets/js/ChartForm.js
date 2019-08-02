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






x2.ChartForm = (function () {

function serialToJSON(serial) {
    var obj = {};
    for(var i in serial) {
        var name = serial[i].name.replace(/.*\[/g,'').replace(']','');
        obj[name] = serial[i].value;
    }

    return obj;
}

function ChartForm (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        controllerUrl: yii.scriptUrl+'/reports/'
    };
    
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.X2Form.call (this, argsDict);
}

ChartForm.prototype = auxlib.create (x2.X2Form.prototype);

ChartForm.prototype._init = function () {
    this.setUpFormBehavior();
};

ChartForm.prototype.setUpFormBehavior = function () {
    var that = this;

    this._form$.find('#submit-button').click(function(e) {
        e.preventDefault();
        that.submitForm();
    });
};

ChartForm.prototype.submitForm = function() {
    var that = this;

    // Convert form to serial array
    var serial = this._form$.serializeArray();

    //Convert form to a json
    var form = serialToJSON(serial);

    // Add a key of the form model name
    var json = {};
    json[this.formModelName] = form;

    $.ajax({
        url: this.controllerUrl + 'createChart',
        data: {
            attributes: JSON.stringify(json)
        },
        dataType: 'json',
        success: function(data) {
            if( data.widget ){
                x2.chartCreator.closeDialog();
                x2.forms.clearForm(that._form$, true);
                that._form$.find('.confirmed').removeClass('confirmed');

                $(data.widget).appendTo(
                    $('#data-widgets-container-inner')
                ).css('opacity', 0.0)
                .animate({
                    opacity: 1.0
                }, 400);

            } else {
                that.highlightErrors(data);  
            }
        }
    });
}

ChartForm.prototype.highlightErrors = function(errors) {
    x2.forms.clearErrorMessages(this._form$);

    var errorList = [];
    for (var key in errors) {
        this._form$.find("#"+key).addClass('error');
        errorList = errorList.concat(errors[key]);
    }

    this._form$.append ( 
        x2.forms.errorSummary ('', errorList) 
    );
}


ChartForm.prototype.select = function (id) {
    return this._form$.find('#'+this.formModelName+'_'+id);
};


return ChartForm;

}) ();
