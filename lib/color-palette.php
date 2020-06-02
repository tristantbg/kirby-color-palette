<?php

namespace SylvainJule;

use League\ColorExtractor\Color;
use League\ColorExtractor\ColorExtractor as Extractor;
use League\ColorExtractor\Palette;

class ColorPalette {

	public static function extractColor($image, $limit = 10, $mode = 'dominant', $size = 400, $fallbackColor = '#ffffff') {
		$thumb     = $image->resize($size)->save();
		$root      = $thumb->root();
		$palette   = Palette::fromFilename($root, Color::fromHexToInt($fallbackColor));
		$extractor = new Extractor($palette);
		$colors    = $extractor->extract($limit);

    $toHex = function($value) {
			return Color::fromIntToHex($value);
		};
  	$colors    = array_map($toHex, $colors);

    $getComplementary = function($value) {
      return self::getComplementary($value);
    };
    $colors = array_map($getComplementary, $colors);

    return $colors;

	}

  public static function color_inverse($color){
    $color = str_replace('#', '', $color);
    if (strlen($color) != 6){ return '000000'; }
    $rgb = '';
    for ($x=0;$x<3;$x++){
        $c = 255 - hexdec(substr($color,(2*$x),2));
        $c = ($c < 0) ? 0 : dechex($c);
        $rgb .= (strlen($c) < 2) ? '0'.$c : $c;
    }
    return '#'.$rgb;
  }

  public static function getComplementary($hex) {

    $colors = [];

    $colors[] = $hex;
    $hsl = self::hexToHsl($hex);

    $hsl[0] = ($hsl[1] * 360 - 180) / 360;
    $hsl[1] = ($hsl[1] * 100) < 50 ? ($hsl[1] * 100 + 25) / 100 : ($hsl[1] * 100 - 25) / 100;
    $hsl[2] = ($hsl[2] * 100) < 50 ? ($hsl[2] * 100 + 50) / 100 : ($hsl[2] * 100 - 50) / 100;

    $colors[] = self::hslToHex($hsl);
    // $colors[] = self::color_inverse($hex);

    return $colors;
  }

  public static function hexToHsl($hex) {
        $hex = str_replace('#', '', $hex);
        $hex = array($hex[0].$hex[1], $hex[2].$hex[3], $hex[4].$hex[5]);
        $rgb = array_map(function($part) {
            return hexdec($part) / 255;
        }, $hex);

        $max = max($rgb);
        $min = min($rgb);

        $l = ($max + $min) / 2;

        if ($max == $min) {
            $h = $s = 0;
        } else {
            $diff = $max - $min;
            $s = $l > 0.5 ? $diff / (2 - $max - $min) : $diff / ($max + $min);

            switch($max) {
                case $rgb[0]:
                    $h = ($rgb[1] - $rgb[2]) / $diff + ($rgb[1] < $rgb[2] ? 6 : 0);
                    break;
                case $rgb[1]:
                    $h = ($rgb[2] - $rgb[0]) / $diff + 2;
                    break;
                case $rgb[2]:
                    $h = ($rgb[0] - $rgb[1]) / $diff + 4;
                    break;
            }

            $h /= 6;
        }

        return array($h, $s, $l);
    }

    public static function hslToHex($hsl)
    {
        list($h, $s, $l) = $hsl;

        if ($s == 0) {
            $r = $g = $b = 1;
        } else {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;

            $r = self::hue2rgb($p, $q, $h + 1/3);
            $g = self::hue2rgb($p, $q, $h);
            $b = self::hue2rgb($p, $q, $h - 1/3);
        }

        return '#' . self::rgb2hex($r) . self::rgb2hex($g) . self::rgb2hex($b);
    }

    public static function hue2rgb($p, $q, $t) {
        if ($t < 0) $t += 1;
        if ($t > 1) $t -= 1;
        if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
        if ($t < 1/2) return $q;
        if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;

        return $p;
    }

    public static function rgb2hex($rgb) {
        return str_pad(dechex($rgb * 255), 2, '0', STR_PAD_LEFT);
    }

}
