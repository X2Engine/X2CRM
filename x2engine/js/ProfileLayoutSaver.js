/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2022 X2 Engine Inc.
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
 * Class to manage the profile layout editor
 */

function ProfileLayoutSaver(argsDict) {
    var defaultArgs = {
        saveProfileLayoutUrl: '',
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this._init();
}

ProfileLayoutSaver.prototype._saveProfileLayout = function (groupId, callback) {
    var that = this;
    console.log(that.saveProfileLayoutUrl);
    $.ajax ({
        url: this.saveProfileLayoutUrl,
        data: {
            'groupId': groupId,
        },
        type: 'POST',
        dataType: 'json',
        success: function (data) {
            if (data !== 'failure') {
                alert('saved layout')
                callback ();
            }
        }
    });
}

ProfileLayoutSaver.prototype._setUpCreateWidgetDialog = function () {
    var that = this;
    var dialog$ = $('#save-profile-layout-dialog').dialog ({
        title: 'Save Layout (translate me)',
        autoOpen: false,
        width: 500,
        buttons: [
            {
                text: 'Cancel',
                click: function () {
                    $(this).dialog ('close');
                }
            },
            {
                text: 'Save',
                class: 'highlight',
                click: function () {
                    var groupId = $(this).find ('#groupSelect').val ();
                    if (groupId === undefined) return;
                    var callback = function (){
                        dialog$.dialog ('close'); }; 

                    that._saveProfileLayout (groupId, callback);
                }
            }
        ]
    });

    // save-profile-layout-button
    $('#save-layout').click (function () {
        dialog$.dialog ('open');
    });

    // when the dropdown changes
    $('#currentLayout').change(function () {
        var selectedValue = $('#currentLayout').val();
        $.ajax({
            'url'  : yii.scriptUrl + '/profile/setProfileLayout',
            'type' : 'POST',
            'data' : {
                'selected' : selectedValue
            },
            'success' : function(){
		location.reload();
            },
            'error' : function(error){
                alert('error');
            }
        });
    });
};

ProfileLayoutSaver.prototype._init = function () {
    
    this._setUpCreateWidgetDialog ();
}
