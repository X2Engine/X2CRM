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




/************************************************
* PopUp Child Class
* This class will create Q-tips, highlighting 
* an element with a dashed border. 
*************************************************/
x2.Tours.classes['.popup'] = (function(){

	var PopupTip = function (element) {
	    var defaultArgs = {
	        target: null,
	        highlight: false
	    };

	    x2.Tip.child(this, element, defaultArgs);
	}

	PopupTip.prototype = auxlib.create(x2.Tip.prototype);
	
	PopupTip.prototype.init = function() {

	    // Placeholder Id and Element for the qtip
	    // To keep the event binders, and to avoid jittering, 
	    // the tip is place next to the placeholder once the tip 
	    // is shown
	    this.placeholderId = 'tour-placeholder-'+ this.id;
	    var placeholder = '<div id="'+ this.placeholderId +'"></div>';

	    // Target is the element the tip is attached to
	    this.target = $(this.element.data('target')).first();

	    if (this.target.length == 0) {
	        x2.Tours['delete'] (this);
	        return;
	    }

	    this.target.qtip({
	        content: placeholder,
	        hide: false,
	        show: false,
	        style: {
	            classes: 'x2-tour-tip'
	        }
	    });

	};

	PopupTip.prototype.highlightTarget = function (bool) {
	    if (this.highlight) {
	        this.target.toggleClass('x2-tour-highlight', bool);
	    }
	}

	PopupTip.prototype.hide = function () {
	    // Hide tip
	    this.target.qtip('hide');

	    // Dehighlight target
	    this.highlightTarget(false);
	}

	PopupTip.prototype.open = function () {
	    // Show the qip
	    this.target.qtip('show');

	    // Add the highlight box
	    this.highlightTarget(true);

	    // Replace the placeholder
	    this.element.insertBefore($('#'+this.placeholderId));
	}

	PopupTip.prototype.close = function () {
	    // Destroy tip
	    this.target.qtip('destroy');

	    //destroy the highlight box
	    this.highlightTarget(false);

	    // remove the element
	    this.element.remove();
	}

	return PopupTip;
})();
