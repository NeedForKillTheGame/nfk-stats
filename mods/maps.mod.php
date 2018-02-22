<?php
if (!defined('NFK_LIVE')) die();
define('MAPS_PATH', 'tribes/maps/dump/');
require_once('inc/autoloader.php');
Autoloader::register();
use NFK\MapViewer\MapViewer;
$maps = $db->select('*', 'maps','', true, 'hash');
$files = scandir(MAPS_PATH);
$mapsInfo = array();
foreach ($files as $file) {
    if ($file == '.' || $file == '..') continue;
    $ext = pathinfo(MAPS_PATH . $file, PATHINFO_EXTENSION);
    if ($ext == 'mapa') {
        $hash = md5(file_get_contents(MAPS_PATH . $file));
        if (isset($maps[$hash])) {
            $mapData = json_decode($maps[$hash]['data'], true);
        } else {
            $nfkMap = new MapViewer(MAPS_PATH . $file);
            $mapName = pathinfo(MAPS_PATH . $file, PATHINFO_FILENAME);
            $mapData = null;
            $nfkMap->LoadMap();
            if ($nfkMap) {
                $mapData = array(
                    'author' => $nfkMap->Header->Author,
                    'title' => $nfkMap->Header->MapName,
                    'sizeX' => $nfkMap->Header->MapSizeX,
                    'sizeY' => $nfkMap->Header->MapSizeY,
                    'name' => $mapName,
                    'filename' => $file,
                );
            }
            if ($mapData) {
                $db->insert('maps', array(
                    'hash' => $hash ,
                    'name' => $mapName,
                    'data' => json_encode($mapData),
                ), true, true);
            }
        }
        $mapsInfo[] = $mapData;

    }
}
// GTW: maps
$template->load_template('mod_maps');
$templateMaps = null;
foreach ($mapsInfo as $map) {
    $params = array(
        'author' => htmlspecialchars($map['author']),
        'title' => htmlspecialchars($map['title']),
        'sizeX' => $map['sizeX'],
        'sizeY' => $map['sizeY'],
        'name' => $map['name'],
        'filename' => $map['filename'],
        'urlName' => urlencode($map['name'])
    );
	$template->assign_variables($params);
    $templateMaps .= $template->build('map');
}
$page_title = $dict->data['maps'];
$page_name = $page_title;
// Build Main
$MARKERS = array(
    'G_MAPS' => $templateMaps,
    'PAGES' => $pages,
    'L_NAME' => $dict->data['name'],
    'L_TITLE' => $dict->data['title'],
    'L_AUTHOR' => $dict->data['author'],
    'L_SIZE' => $dict->data['size'],
);
$template->assign_variables($MARKERS);
$content_data .= $template->build('main');
