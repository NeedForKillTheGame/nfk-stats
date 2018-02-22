<?php
if (!defined('NFK_LIVE')) die();
define('MAPS_PATH', 'tribes/maps/dump/');
define('MAPS_IMAGES', 'images/maps/');
define('MAPS_THUMBS', 'images/maps/thumbs');
require_once('inc/autoloader.php');
Autoloader::register();
use NFK\MapViewer\MapViewer;
$isView = $PARAMSTR[2] == 'view';
$mapName = ($PARAMSTR[3] != '') ? urldecode($PARAMSTR[3]) : null;
if ($isView) {
    $mapName = pathinfo($mapName, PATHINFO_FILENAME) . '.mapa';
}
$file = MAPS_PATH . $mapName;
if ($mapName && file_exists($file)) {
    if ($isView) {
        $hash = md5(file_get_contents($file));
        $imageFile = MAPS_IMAGES . $hash . '.png';
        if (file_exists($imageFile)) {
            if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= filemtime($imageFile)) {
                header('HTTP/1.0 304 Not Modified');
                exit;
            }
            $image = imagecreatefrompng($imageFile);
        } else {
            set_time_limit(60);
            $nfkMap = new MapViewer($file);
            $nfkMap->LoadMap();
            $image = $nfkMap->DrawMap();
            if ($image) imagepng($image, $imageFile);
        }
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($imageFile)).' GMT', true, 200);
        header('Expires: 0');
        header("Cache-control: public");
        header("Pragma: public");
        header( "Etag: " . sprintf( '"%s-%s"',  filemtime($imageFile), $hash ) );
        header('Content-Type: image/png;');
        if ($image) imagepng($image);
    } else {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($mapName));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        ob_clean();
        flush();
        readfile($file);
    }
}
exit;


