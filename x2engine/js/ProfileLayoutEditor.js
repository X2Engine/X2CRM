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



;

/**
 * Class to manage the profile layout editor
 */
x2.ProfileLayoutEditor = (function() {

function ProfileLayoutEditor(argsDict) {
    var defaultArgs = {
        defaultWidth: 52,
        settingName: 'columnWidth',
        columnWidth: null,
        margin: null,
        minWidths: [24, 24],

        // selections that are resized with the first column
        column1: [
            '#profile-section-1', 
            '#activity-feed-container-outer', 
            '#profile-widgets-container-2'
        ],

        // selections that are resized with the second column
        column2: [
            '#profile-layout-editor #section-2', 
            '#profile-widgets-container'
        ],
        //Element that is resized / dragged
        draggable: '#profile-section-1',

        //overall container for the widget
        container: '#profile-layout-editor',
        
        // middle icon indicator
        indicator: '.indicator',

        // Button to open the editor
        editLayoutButton: '#edit-layout',

        // Button to close the editor
        closeButton: '.close-button',

        // Button to reset the columnWidth
        resetButton: '.reset-button',

        //URL for the misc settings action
        miscSettingsUrl: null 
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.LayoutEditor.call (this, argsDict);

}

ProfileLayoutEditor.prototype = auxlib.create (x2.LayoutEditor.prototype);


return ProfileLayoutEditor;

})();
