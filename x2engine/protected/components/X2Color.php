<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

/**
 * Color utilities (unused)
 * 
 * @package X2CRM.components 
 */
class X2Color {

	// convert HEX color to RGB values
	public static function hex2rgb($color) {
		if($color[0] == '#')
			$color = substr($color, 1);
		
		if(strlen($color) === 6)
			list($r,$g,$b) = array($color[0].$color[1],$color[2].$color[3],$color[4].$color[5]);
		elseif (strlen($color) === 3)
			list($r,$g,$b) = array($color[0].$color[0],$color[1].$color[1],$color[2].$color[2]);
		else
			return false;
		
		return array(hexdec($r),hexdec($g),hexdec($b));
	}
	
	public static function rgb2hex($r,$g,$b) {
		$r = dechex($r);
		$g = dechex($g);
		$b = dechex($b);
		if(strlen($r) < 2)
			$r = '0'.$r;
		if(strlen($g) <2 )
			$g = '0'.$g;
		if(strlen($b) < 2)
			$b = '0'.$b;
		
		return $r.$g.$b;
	}
	
	public static function gradientCss($color1,$color2) {
		return "background:$color1;
background:-moz-linear-gradient(top, $color1 0%, $color2 100%);
background:-webkit-gradient(linear, left top, left bottom, color-stop(0%,$color1), color-stop(100%,$color2));
background:-webkit-linear-gradient(top, $color1 0%,$color2 100%);
background:-o-linear-gradient(top, $color1 0%,$color2 100%);
background:-ms-linear-gradient(top, $color1 0%,$color2 100%);
background:linear-gradient(to bottom, $color1 0%,$color2 100%);
filter:progid:DXImageTransform.Microsoft.gradient( startColorstr='$color1', endColorstr='$color2',GradientType=0);";
	}
}
?>