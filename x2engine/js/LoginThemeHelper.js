x2.LoginThemeHelper = (function(){

	var themes = [
		{ name: 'blue',   colors: ['#233D5F', '#06090F'] },
		{ name: 'green',  colors: ['#005C43', '#092D22'] },
		{ name: 'yellow', colors: ['#A77022', '#0F0A06'] },
		{ name: 'orange', colors: ['#FE7F00', '#783B00'] },
		{ name: 'red',    colors: ['#7A1919', '#0F0606'] },
		{ name: 'purple', colors: ['#623056', '#0C0007'] },
		{ name: 'pink',   colors: ['#BB4E86', '#583746'] },
		{ name: 'white',  colors: ['#D5D5D5', '#B1B1B1'] },
		{ name: 'grey',   colors: ['#252525', '#010101'] },
	];


	var LoginThemeHelper = function(argsDict) {
		// argsDict = $.parseJSON(argsDict);
		var defaultArgs = {
		    themeSelector: '.theme-selection',
			formSelector: '#dark-theme-form', // the id of the profile associated with this widget
			themeColorCookie: '', // the name of the associated widget class
			cookieLength: '', // the url used to call the set profile widget property action
			open: false,
			currentColor: themes[0].name, 
			currentThemeBG: ['#252525', '#010101']
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
		$('<span></span>').appendTo( this.element$ ).
			attr('class', 'theme-choice').
			attr('id', theme.name).
			attr('value', JSON.stringify(theme.colors) ).
			css('background', theme.colors[0]);
	}


	LoginThemeHelper.prototype.applyTheme = function(scheme) {
		var colors = scheme.colors;
		var name = scheme.name;

		$('.background').css('background', 
			'radial-gradient('+colors[0]+', '+colors[1]+')'
		);

		// Special case for the white color to have blue buttons
		if (name == 'white') {
			colors = themes[0].colors;
		}

		$('.x2-blue').css({
			background: colors[0],
			borderColor: colors[0]
		});

		$('a').css({
			color: colors[0]
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

	LoginThemeHelper.prototype.setUpFade  = function () {
		$('#signin-button').click(function() {
			$('#login-page').css({
				transition: 'opacity .5s',
				opacity: 0.0,
			});

			function loop() {
				$('.background .stripe').delay(200).
					animate({
						opacity: 0.7
					}, 1000).
					// delay(300).
					animate({
						opacity: 0.5
					}, 1000, 'swing', loop);		
			};
			loop();
		});
	};

	return LoginThemeHelper;
})();
