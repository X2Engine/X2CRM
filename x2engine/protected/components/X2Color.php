<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

/**
 * Color utilities (unused)
 * 
 * @package application.components 
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

    public static function rgb2hex2($r, $g=-1, $b=-1) {
        if (is_array($r) && sizeof($r) == 3)
            list($r, $g, $b) = $r;

        $r = intval($r); 
        $g = intval($g);
        $b = intval($b);

        $r = dechex($r<0?0:($r>255?255:$r));
        $g = dechex($g<0?0:($g>255?255:$g));
        $b = dechex($b<0?0:($b>255?255:$b));

        $color = (strlen($r) < 2?'0':'').$r;
        $color .= (strlen($g) < 2?'0':'').$g;
        $color .= (strlen($b) < 2?'0':'').$b;
        return '#'.$color;
    }

    public static function hex2rgb2($color) {
        if ($color[0] == '#')
            $color = substr($color, 1);

        if (strlen($color) == 6)
            list($r, $g, $b) = array($color[0].$color[1],
                                     $color[2].$color[3],
                                     $color[4].$color[5]);
        else if (strlen($color) == 3)
            list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
        else
            return false;

        $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

        return array($r, $g, $b);
    }

    /*public static function lighten ($r, $g, $b, $percent=-5) {
        $colors = self::rgb2hex2 (array_map (function ($a) {
            return $a > 255 ? 255 : $a;
        }, array (($percent / 100) * $r, ($percent / 100) * $g, ($percent / 100) * $b
        )));
        return $colors;
    }*/
	
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
