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




x2.Attachments = (function () {

function Attachments (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        translations: {
            filetypeError: '"{x}" is not an allowed filetype.'
        }
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    // array with disallowed extensions
    this._illegal_ext = ['exe','bat','dmg','js','jar','swf','php','pl','cgi','htaccess','py'];	
    this._fileIsUploaded = false;
    this._submitButtonSelector = '#submitAttach';
}

/**
 * @return bool True if a file with a valid extension has been uploaded, false otherwise
 */
Attachments.prototype.fileIsUploaded = function () {
    return this._fileIsUploaded;
};

Attachments.prototype.checkName = function (evt) {
    var elem = evt;

    var re = this.checkFileName (evt);

	// if re is 1, the extension isn't illegal
	if (re) {
		// enable submit
        this._fileIsUploaded = true;
		$(this._submitButtonSelector).removeAttr('disabled');
	} else {
        this._fileIsUploaded = false;
		// delete the file name, disable Submit, Alert message
		elem.value = '';
		$(this._submitButtonSelector).attr('disabled','disabled');

		var filenameError = this.translations.filetypeError;
		var ar_ext = this.getFileExt (evt);
		alert(filenameError.replace('{X}',ar_ext));
	}
};

Attachments.prototype.checkFileName = function (evt) {
    var elem = evt.target;

	// - www.coursesweb.net
	// get the file name and split it to separe the extension
	var name = elem.value;
	var ar_name = name.split('.');

	var ar_ext = ar_name[ar_name.length - 1].toLowerCase();

	// check the file extension
	var re = 1;
	for(var i in this._illegal_ext) {
		if(this._illegal_ext[i] == ar_ext) {
			re = 0;
			break;
		}
	}

    return re === 1;
};

Attachments.prototype.getFileExt = function (evt) {
    var name = evt.target.value;
	var ar_name = name.split('.');
	var ar_ext = ar_name[ar_name.length - 1].toLowerCase();
    return ar_ext;
};


return Attachments;

}) ();
