<?php


if (!defined('NFK_LIVE')) die();
define('MAPS_IMAGES', 'images/maps/');
define('MAPS_THUMBS', 'images/maps/thumbs/');

require_once('inc/autoloader.php');
Autoloader::register();
use NFK\MapViewer\MapViewer;

$i = 2;
$matchID = $PARAMSTR[$i++];
if (!is_numeric($matchID)) header("Location: $_SERVER[HTTP_REFERER]");
$match = $db->select("demo","matchList","WHERE matchID=$matchID");
$match = $match[0];

$isThumb = $PARAMSTR[$i++] == 'thumb'; // display resized thumb instead of full map
if (!$isThumb) $i--;

$mapName = $PARAMSTR[$i++];


$file = "demos/{$match['demo']}";

if (!$match['demo'] || !file_exists($file))
	die("Demo file does not exist!");

// read demo map
$nfkMap = new MapViewer($file);
$nfkMap->LoadMap();
$hash = $nfkMap->GetHash();

$imageFile = ($isThumb ? MAPS_THUMBS : MAPS_IMAGES) . $hash . ($isThumb ? '.jpg' : '.png');

// if image file not exists
if (!file_exists($imageFile))
{
	set_time_limit(60);


	if ($isThumb)
	{
		$nfkMap->drawspecialobjects = false;
		$image = $nfkMap->DrawMap();
		// text on image
		$title = sprintf("%s (%sx%s)", $mapName, $nfkMap->Header->MapSizeX, $nfkMap->Header->MapSizeY);
		// create resized image with max size 300px and text
		$im2 = resizeImage($image, 300, $title);
		// save jpg image with 75 quality
		imagejpeg($im2, $imageFile, 75);
	}
	else
	{
		$image = $nfkMap->DrawMap();
		// save image
		imagepng($image, $imageFile);
	}
}

header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($imageFile)).' GMT', true, 200);
header('Expires: 0');
header("Cache-control: public");
header("Pragma: public");
header( "Etag: " . sprintf( '"%s-%s"',  filemtime($imageFile), $hash ) );
header('Content-Type: image/' . ($isThumb ? 'jpeg' : 'png') . ';');

// display file contents
ob_clean();
flush();
readfile($imageFile);

exit();









// return resized image
function resizeImage($src, $max_size = 200, $text = false)
{
	list($tn_width, $tn_height) = getpropsize(imagesx($src), imagesy($src), $max_size);
	
	
	$im=imagecreatetruecolor($tn_width,$tn_height);
	imagecopyresampled($im,$src,0,0,0,0,$tn_width, $tn_height,imagesx($src), imagesy($src));
	
	// text
	if ($text)
	{
		// black
		$bar_color = imagecolorallocatealpha($im, 0, 0, 0, 80);
		
		imagefilledrectangle($im, 0, $tn_height-20, $tn_width, $tn_height, $bar_color);
		
		$txt_color = imagecolorallocate($im, 255, 255, 255);
		$txt_file = "css/arial.ttf";
		if (!file_exists($txt_file))
			die('can\'t find font arial.ttf');
			
		$txt_fontsize = 10.5;
		imagettftext ($im, $txt_fontsize, 0,  10, $tn_height-6, $txt_color, $txt_file, $text);
	}
	return $im;
}
// return proportional small size from the source size
function getpropsize($width, $height, $max)
{
	if ($width <= $max and $height <= $max)
		return array($width, $height);
		
	$lager = ($width > $height) ? $width : $height; //  сторона, которая длиннее
	
	$k = $lager / $max; // во сколько раз уменьшить
	$w = @round($width / $k); // 1%
	$h = @round($height / $k); // 1%
	return array($w, $h);
}
