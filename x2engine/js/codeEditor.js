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
 * @edition:ent
 */

if (typeof x2 === "undefined")
    x2 = {};
if (typeof x2.codeEditor === "undefined")
    x2.codeEditor = {};

x2.codeEditor.createFile = function() {
    var filename = prompt(x2.codeEditor.translations["enter filename"]);
    if (filename) {
        var throbber = auxlib.pageLoading();
        $.ajax({
            type: "POST",
            data: {
                operation: "create",
                filename: filename,
            },
            error: function(data) {
                alert(data.responseText);
                throbber.remove();
            },
            success: function(data) {
                // Refresh filetree
                var browser = $('#fileBrowser');
                browser.empty();
                $('<ul>', {id: 'filetree', 'class': 'filetree'}).appendTo(browser).treeview({
                    url: x2.codeEditor.fileTreeUrl,
                    persist: 'cookie',
                    id: 'filetree',
                    animated: 'fast'
                });
                throbber.remove();
            },
        });
    }
}

x2.codeEditor.lint = function() {
    if (typeof x2.codeEditor.currentFile !== "undefined") {
        var throbber = auxlib.pageLoading();
        $.ajax({
            type: "POST",
            data: {
                operation: "lint",
                filename: x2.codeEditor.currentFile,
            },
            error: function(data) {
                alert(data.responseText);
            },
            complete: function() {
                throbber.remove();
            }
        });
    } else {
        alert(x2.codeEditor.translations["no source file"]);
    }
}

x2.codeEditor.saveSource = function() {
    if (typeof x2.codeEditor.currentFile !== "undefined") {
        var throbber = auxlib.pageLoading();
        var contents = window.codeEditor.getData();
        $.ajax({
            type: "POST",
            data: {
                operation: "save",
                filename: x2.codeEditor.currentFile,
                contents: contents
            },
            error: function(data) {
                alert(data.responseText);
            },
            complete: function() {
                throbber.remove();
            }
        });
    } else {
        alert(x2.codeEditor.translations["no source file"]);
    }
}

x2.codeEditor.instantiateCodeEditor = function(insertableAttributes) {
    var insertableAttributes = typeof insertableAttributes === "undefined" ? 
        x2.insertableAttributes : insertableAttributes; 

    window.codeEditor = createCKEditor("input",{
        // insertableAttributes: insertableAttributes,
        toolbar:"MyCodeEditorToolbar",
        fullPage:true,
        startupMode: "source",
        autoParagraph:false,
        htmlEncodeOutput: false,
        entities: false,
        toolbarCanCollapse: false,
        height:600
    }, x2.codeEditor.finishSetup);
    // Allow PHP tags in CKEditor
    //window.codeEditor.config.protectedSource.push(/<\?php[\s\S]*?\?>/g);
    window.codeEditor.config.protectedSource.push(/<\?php[\s\S]+?(\?>)?/g);
}

x2.codeEditor.finishSetup = function() {
    var themePreference = $.cookie("x2-codeEditor-keyMap");
    if (themePreference && themePreference === "vim")
        $('.cke_button__togglevimbindings_icon').click();

    var themePreference = $.cookie("x2-codeEditor-theme");
    if (themePreference && themePreference === "seti")
        $('.cke_button__toggledarktheme_icon').click();

    // Set up autolint
	if($.browser.msie)
		return;

	// lint after 1.5 seconds when the user is done typing
	window.codeEditor.document.on("keyup",function(e) {
		clearTimeout(lintingTimer);
		lintingTimer = setTimeout(x2.codeEditor.lint, 1500);
	});
	window.codeEditor.on("saveSnapshot",function(e) {
		clearTimeout(lintingTimer);
		lintingTimer = setTimeout(x2.codeEditor.lint, 1500);
	});
	window.codeEditor.document.on("keydown",function(){ clearTimeout(lintingTimer); });
}


x2.codeEditor.toggleDarkTheme = function() {
    var editor = $('.CodeMirror')[0].CodeMirror;
    if (typeof x2.codeEditor.theme === 'undefined' || x2.codeEditor.theme === 'default') {
        editor.setOption('theme', 'seti');
        x2.codeEditor.theme = 'seti';
        $.cookie('x2-codeEditor-theme', 'seti');
    } else {
        editor.setOption('theme', 'default');
        x2.codeEditor.theme = 'default';
        $.cookie('x2-codeEditor-theme', 'default');
    }
}

x2.codeEditor.setMode = function(mode) {
    var allowedModes = ['application/x-httpd-php', 'javascript', 'sql', 'css', 'sass'];
    if ($.inArray(mode, allowedModes) !== -1) {
        var editor = $('.CodeMirror')[0].CodeMirror;
        editor.setOption('mode', mode);
    }
}

x2.codeEditor.toggleVimBindings = function() {
    var editor = $('.CodeMirror')[0].CodeMirror;
    var keyMap = editor.getOption('keyMap');
    if (keyMap === 'vim') {
        editor.setOption('keyMap', 'default');
        $.cookie('x2-codeEditor-keyMap', 'default');
        return 'default';
    } else {
        editor.setOption('keyMap', 'vim');
        $.cookie('x2-codeEditor-keyMap', 'vim');
        return 'vim';
    }
}
