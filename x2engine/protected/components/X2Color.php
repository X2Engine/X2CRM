<?php
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
 * Color utilities (unused)
 * 
 * @package application.components 
 */
class X2Color {

    /**
     * Converts HEX color to YIQ color 
     */
    public static function rgb2yiq ($color) {
        // RGB -> YIQ transformation matrix
        $matrixA = array (
            array (0.299, 0.587, 0.144),
            array (0.595716, -0.274453, -0.321263),
            array (0.211456, -0.522591, 0.31135),
        );
        $product = array ();
        $matrixB = $color;
        foreach ($matrixA as $row) {
            $product[] = $row[0] * $matrixB[0] + $row[1] * $matrixB[1] + $row[2] * $matrixB[2]; 
        }
        return $product;
    }

    /**
     * @return int
     */
    public static function getColorBrightness ($hexColor) {
        $rgb = self::hex2rgb ($hexColor);
        if (!is_array ($rgb)) {
            return false;
        }
        $yiq = self::rgb2yiq ($rgb);
        return $yiq[0];
    }

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

    public static function rgb2hex2($r, $g=-1, $b=-1, $hash = '#') {
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
        return $hash.$color;
    }

    public static function hex2rgb2($color) {
        if ($color[0] == '#')
            $color = substr($color, 1);

        if (strlen($color) >= 6)
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

    /**
     * Adjusts the brightness of a color
     * @param string $color The hexcode of the target color (with or without hash)
     * @param float $percent The adjustment to be made. < 0 indicated darker, >0 indicated lighter
     * @param boolean $smartMode If true, the color difference will reverse when necessary. 
     * @return string the adjusted hexcode
     */
    public static function brightness($color, $percent, $smartMode=false){
        if(!$color)
            return '';

        $color = self::hex2rgb2($color);
        if (!$color)
            return '';

        if ($smartMode) {
            $hsb = self::rgbtohsb($color);
            if ($hsb[2] > 0.6) {
                $percent *= -1;
            }
        }


        $color[0] += 255*$percent;
        $color[1] += 255*$percent;
        $color[2] += 255*$percent;
        return self::rgb2hex2($color[0], $color[1], $color[2]);

    }

    public static function opaque($color, $percent = 0.9){
        if(!$color)
            return '';

        $color = self::hex2rgb2($color);

        return "rgba($color[0], $color[1], $color[2], $percent)" ;

    }

    public static function smartText($context, $text) {
        if(!$text || !$context){
            return '';
        }

        $contextRGB = self::hex2rgb2($context);
        $textRGB = self::hex2rgb2($text);

        $contextHSB = self::rgbtohsb($contextRGB);
        $textHSB = self::rgbtohsb($textRGB);

        $diff = $contextHSB[2] - $textHSB[2];
        if ( abs($diff) > 0.56 ) {

            return $text;
        }

        $h = $contextHSB[2] < 0.6 ? 0.8 : -0.8;
    
        $textHSB[2] += $h;

        $textRGB = self::hsbtorgb($textHSB);
        $text = self::rgb2hex2($textRGB);


        return $text;
    }

    public static function rgbtohsb( $rgb ) {
        $red = $rgb[0];
        $green = $rgb[1];
        $blue = $rgb[2];

        $oldR = $red;
        $oldG = $green;
        $oldB = $blue;


        $red /= 255;
        $green /= 255;
        $blue /= 255;

        $max = max( $red, $green, $blue );
        $min = min( $red, $green, $blue );

        $hue;
        $sat;
        $lum = ( $max + $min ) / 2;
        $diff = $max - $min;

            if( $diff == 0 ){
                $hue = $sat = 0;
            } else {
                $sat = $diff / ( 1 - abs( 2 * $lum - 1 ) );

            switch( $max ){
                    case $red:
                        $hue = 60 * fmod( ( ( $green - $blue ) / $diff ), 6 ); 
                            if ($blue > $green) {
                            $hue += 360;
                        }
                        break;

                    case $green: 
                        $hue = 60 * ( ( $blue - $red ) / $diff + 2 ); 
                        break;

                    case $blue: 
                        $hue = 60 * ( ( $red - $green ) / $diff + 4 ); 
                        break;
                }                               
        }

        return array( round( $hue, 2 ), round( $sat, 2 ), round( $lum, 2 ) );
    }

    public static function hsbtorgb( $hsb ){

        foreach ($hsb as $i => $v){
            $hsb[$i] = ($v < 1) ? $v : 1;
            $hsb[$i] = ($v > 0) ? $v : 0;
        }

        $hue = $hsb[0];
        $sat = $hsb[1];
        $lum = $hsb[2];

        $red; 
        $green; 
        $blue;

        $c = ( 1 - abs( 2 * $lum - 1 ) ) * $sat;
        $x = $c * ( 1 - abs( fmod( ( $hue / 60 ), 2 ) - 1 ) );
        $m = $lum - ( $c / 2 );

        if ( $hue < 60 ) {
            $red = $c;
            $green = $x;
            $blue = 0;

        } else if ($hue < 120) {
            $red = $x;
            $green = $c;
            $blue = 0;      

        } else if ($hue < 180) {
            $red = 0;
            $green = $c;
            $blue = $x;       

        } else if ($hue < 240) {
            $red = 0;
            $green = $x;
            $blue = $c;

        } else if ($hue < 300) {
            $red = $x;
            $green = 0;
            $blue = $c;

        } else {
            $red = $c;
            $green = 0;
            $blue = $x;
        }

        $red = ( $red + $m ) * 255;
        $green = ( $green + $m ) * 255;
        $blue = ( $blue + $m  ) * 255;

        return array( floor( $red ), floor( $green ), floor( $blue ) );
    }
	
	public static function gradientCss($color1,$color2) {
		return "
            background:$color1;
            background:-moz-linear-gradient(top, $color1 0%, $color2 100%);
            background:-webkit-gradient(linear, left top, left bottom, color-stop(0%,$color1), color-stop(100%,$color2));
            background:-webkit-linear-gradient(top, $color1 0%,$color2 100%);
            background:-o-linear-gradient(top, $color1 0%,$color2 100%);
            background:-ms-linear-gradient(top, $color1 0%,$color2 100%);
            background:linear-gradient(to bottom, $color1 0%,$color2 100%);
            filter:progid:DXImageTransform.Microsoft.gradient( startColorstr='$color1', endColorstr='$color2',GradientType=0);";
	}

    public static function HSVToRGB ($h, $s, $v) {
        $h *= 360;
        $c = $v * $s;
        $hPrime = $h / 60;
        $x = $c * (1 - abs (fmod ($hPrime, 2) - 1));
        switch (floor ($hPrime)) {
            case 0:
                list ($r, $g, $b) = array ($c, $x, 0);
                break;
            case 1:
                list ($r, $g, $b) = array ($x, $c, 0);
                break;
            case 2:
                list ($r, $g, $b) = array (0, $c, $x);
                break;
            case 3:
                list ($r, $g, $b) = array (0, $x, $c);
                break;
            case 4:
                list ($r, $g, $b) = array ($x, 0, $c);
                break;
            case 5:
                list ($r, $g, $b) = array ($c, 0, $x);
                break;
        }
        $r *= 255;
        $g *= 255;
        $b *= 255;
        return array (floor ($r), floor ($g), floor ($b));
    }

    public static function generatePalette ($count, $seed=null, $s=0.95, $v=0.99) {
        $goldenRatio = 0.6180339887;
        if ($seed === null)
            $h = rand () / getrandmax ();
        else
            $h = $seed;
        $colors = array ();
        for ($i = 0; $i < $count; $i++) {
            $h += $goldenRatio;
            $h = fmod ($h, 1);
            $colors[] = self::HSVToRGB ($h, $s, $v);
        }
        return $colors;
    }
}
?>
