<?php
namespace models;
/**
 * @var \skin $template
 * @var Map $map
 * @var string $content_data
 * @var array $PARAMSTR
 */
$maps = Map::findAll();
$content_data .= $template->render('index', array('maps' => $maps));
$page_title = $page_name = 'Map list';