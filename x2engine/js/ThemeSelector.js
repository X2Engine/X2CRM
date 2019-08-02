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
 * Class to handle the theme Selector
 */

x2.ThemeSelector = (function(){
	function ThemeSelector(argsDict) {
		var defaultArgs = {
			defaults: ['Default', 'Terminal'],
			active: null,
			user: null,
			isAdmin: false,
			translations: {}
		};

		auxlib.applyArgs(this, defaultArgs,argsDict);

		this.active = this.active || this.defaults[0];

		this.active$ = this.getSelector(this.active);

		if ( $(this.active$).length == 0 ) {
			this.active = this.defaults[0];
			this.active$ = this.getSelector(this.active);
		}

		this.fillColorFields(this.active$);
		this.setUpClickBehavior();
	}

	ThemeSelector.prototype.getSelector = function(themeName) {
		return '.scheme-container[name="'+themeName+'"]';
	}

	ThemeSelector.prototype.changeSelectBox = function(parent, name) {
        var element = $(parent).find('.hidden#'+name).attr('value');
        var options = $('select#'+name+' > option');
        options.removeAttr('selected');
        options.filter('[value=\"'+element+'\"]').attr('selected','selected');
    }


	ThemeSelector.prototype.fillColorFields = function(themeBox) {

        $('.color-picker-input').val('');

        // this.changeSelectBox(themeBox, 'backgroundTiling');
        // this.changeSelectBox(themeBox, 'backgroundImg');

        $(themeBox).find('.scheme-color').each( function(){
            var name = $(this).attr('name');
            var color = $(this).attr('color');
            $('input#preferences_'+name).val(color);
        });

        $('.color-picker-input').trigger('blur');
        $('.color-picker-input').trigger('change');

        var themeName = $(themeBox).attr('name')
        $('input#themeName').val(themeName);
        $('.scheme-container').removeClass('active');
        $(themeBox).addClass('active');

        var user = $(themeBox).find('#uploadedBy').attr('value');

        if (!this.isAdmin && this.user !== user || $.inArray (themeName, this.defaults) >= 0) {
            $('.color-picker-input').attr('readonly','').attr(
                'title', this.translations.createNew );
            $('.sp-replacer.sp-light').hide();
            x2.forms.disableButton ($('#prefs-delete-theme-button, #prefs-save-theme-button'));
        } else {
            x2.forms.enableButton ($('#prefs-delete-theme-button, #prefs-save-theme-button'));
        }
	};


	ThemeSelector.prototype.setUpClickBehavior = function() {
		var that = this;
		$('.scheme-container').click( function() { 
			if ($(that.active$).attr('name') == $(this).attr('name')) {
				return;
			}

            $('#settings-form input[name="regenerate-theme"]').val (0);
            $('#settings-form input[name="preferences[themeName]"]').val ($(this).attr ('name'));
			that.active = this; 

			$('#settings-form').submit();
		} );

	};

	return ThemeSelector;
})();
