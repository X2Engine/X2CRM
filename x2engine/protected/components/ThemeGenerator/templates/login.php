<?php 
	return "

	body#body-tag {
		background: $colors[background]
	}

	#body-tag .container{
		background: $colors[content]
		border-color: $colors[border]
	}

	#body-tag #app-title {
		color: $colors[text]
	}

	#body-tag input {
		border-color: $colors[lighter_content]
	}


	#body-tag .background {
		background: radial-gradient( $colors[background_hex], $colors[darker_background_hex] );
	}

	#body-tag #full-name {
		color: $colors[text]
	}


"; ?>