<?php

class Realpath {
	
	/**
	 * Format a path so that it is platform-independent.
	 * 
	 * @param string $path
	 * @return string 
	 */
	public static function fmt($path) {
		return implode(DIRECTORY_SEPARATOR,explode('/',$path));
	}
}

?>
