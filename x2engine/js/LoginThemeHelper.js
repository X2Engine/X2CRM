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




x2.LoginThemeHelper = (function(){

    var themes = [
//        {
//            "colors": [
//                '#3F75BE',
//                '#3F75BE',
//                '#F4F4F4',
//            ],
//            "name": "lightGray"
//        },
//        {
//            "colors": [
//                '#3F75BE',
//                '#3F75BE',
//                '#FFF'
//            ],
//            "name": "white"
//        },
        {
            "colors": [
                '#255296'
            ],
            "name": "blue"
        },
        {
            "colors": [
                "#005C43"
            ],
            "name": "green"
        },
        {
            "colors": [
                "#A77022",
                13
            ],
            "name": "yellow"
        },
        {
            "colors": [
                "#FE7F00",
                18
            ],
            "name": "orange"
        },
        {
            "colors": [
                "#7A1919"
            ],
            "name": "red"
        },
        {
            "colors": [
                "#623056"
            ],
            "name": "purple"
        },
        {
            "colors": [
                "#BB4E86"
            ],
            "name": "pink"
        },
        {
            "colors": [
                "#E0E0E0"
            ],
            "name": "white"
        },
        {
            "colors": [
                "#252525"
            ],
            "name": "grey"
        }
    ];

	var LoginThemeHelper = function(argsDict) {
		// argsDict = $.parseJSON(argsDict);
		var defaultArgs = {
		    themeSelector: '.theme-selection',
			formSelector: '#dark-theme-form', // the id of the profile associated with this widget
			themeColorCookie: '', // the name of the associated widget class
			cookieLength: '', // the url used to call the set profile widget property action
			open: false,
                        loginFormDark: false,
			loginFormDarkCookie: '',
			currentColor: themes[7].name, 
			currentThemeBG: ['#252525', '#010101', '#131313']
		};
		auxlib.applyArgs (this, defaultArgs, argsDict);
		
		themes.push( { name: 'theme', colors: this.currentThemeBG } );


		if( !this.currentColor )
			this.currentColor = defaultArgs.currentColor;

		this.element$ = $(this.themeSelector);
		this.form$ = $(this.formSelector);

		for(var i in themes) {
            		if (themes.hasOwnProperty (i)) this.appendTheme(themes[i]);
		}

		this.setUpClickBehavior();
		var that = this;
		var currentColorArr = themes.filter( function(d) { return d.name == that.currentColor } );
		this.applyTheme( currentColorArr[0]);

		this.setUpThemeSwitch();
		this.setUpFade();

		if( this.open ) {
			this.element$.show();
		}

               
	}

	LoginThemeHelper.prototype.appendTheme = function(theme) {
        var color = theme.name === 'white' ? 
            tinycolor (theme.colors[0]).darken (8).toString () : theme.colors[0];
		$('<span></span>').appendTo( this.element$ ).
			attr('class', 'theme-choice').
			attr('id', theme.name).
			attr('value', JSON.stringify(theme.colors) ).
			css('background', color);
	}


	LoginThemeHelper.prototype.applyTheme = function(scheme) {
		var colors = scheme.colors;
		var name = scheme.name;

        if (colors[1] && $.type (colors[1]) === 'number') {
            $('.background').css('background', 
                'radial-gradient('+colors[0]+', '+
                    (tinycolor (colors[0]).darken (colors[1]).toString ())+')'
            );
        } else {
            $('.background').css('background', colors[0]);
        }
//		$('.background').css('background', 
//			colors[2]
//		);

		// Special case for the white color to have blue buttons
		if (name == 'white') {
			colors = themes[7].colors;
		}

		$('.x2-blue').attr ('style', x2.css.stringify ({
			'border-color': '#8d8d8d'
		}) + ';' + x2.css.linearGradient (
            tinycolor ('#8d8d8d').lighten (8).toString (), '#8d8d8d'));

		$('#login-form-logo').css({
			color: '#40AD3A',
                        
                });

		$('a').not ('.x2-button').css({
			color: colors[0],
		});

	}

	LoginThemeHelper.prototype.setUpClickBehavior = function(){
		var that = this;

		$('#dark-theme-button').click( function(e) {
			e.preventDefault();
			that.element$.slideToggle('fast');
		});

		this.element$.find('.theme-choice').click( function() {
			var colors = $.parseJSON( $(this).attr('value') );
			var name = $(this).attr('id')
			that.applyTheme({name: name, colors: colors});
			$.cookie(that.themeColorCookie, $(this).attr('id'), { expires: that.cookieLength });

		});
	}

	LoginThemeHelper.prototype.setUpThemeSwitch = function() {
		var that = this;
		this.element$.find('.switch').click(function() {
			that.form$.submit();
		});

	}

       
	LoginThemeHelper.prototype.setUpFade = function () {
		$('#signin-button').click(function() {
//			$('#login-page').css({
//				transition: 'opacity .5s',
//				opacity: 0.0,
//			});

//			function loop() {
//				$('.background .stripe').delay(200).
//					animate({
//						opacity: 0.7
//					}, 1000).
//					// delay(300).
//					animate({
//						opacity: 0.5
//					}, 1000, 'swing', loop);		
//			};
//			loop();
		});
	};

	return LoginThemeHelper;
})();
