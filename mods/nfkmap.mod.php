<?php
namespace models;
/**
 * @var \skin $template
 * @var Map $map
 * @var string $content_data
 * @var array $PARAMSTR
 */
if (empty($PARAMSTR[2])) throw new \Exception('Map is not set');
$id = (int)$PARAMSTR[2];
$map = Map::load($id);

$content_data .= $template->render('index', array('map' => $map));
$page_title = $page_name = 'Map ' . $map->name;